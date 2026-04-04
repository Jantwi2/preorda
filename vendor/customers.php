<?php
session_start();
require_once("../controllers/user_controller.php");

// Check if user is logged in and is a vendor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../view/login.php');
    exit();
}

$vendor_id = $_SESSION['vendor_id'];

// Get vendor information from session
$vendor_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Vendor';
$business_name = isset($_SESSION['business_name']) ? $_SESSION['business_name'] : 'My Store';
$vendor_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Get CRM Customer Data
$customers = get_vendor_customers_ctr($vendor_id);
if (!$customers) $customers = [];

// Calculate overall metrics for CRM header
$total_unique_customers = count($customers);
$total_lifetime_revenue = 0;
$total_vip_customers = 0;

foreach ($customers as $c) {
    $total_lifetime_revenue += $c['lifetime_value'];
    if ($c['total_orders'] >= 3 || $c['lifetime_value'] >= 1000) {
        $total_vip_customers++;
    }
}

// Get initials for profile photo
$name_parts = explode(' ', $vendor_name);
$initials = '';
if (count($name_parts) >= 2) {
    $initials = strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
} else {
    $initials = strtoupper(substr($vendor_name, 0, 2));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer CRM - PreOrda Vendor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #0C0C0C;
            color: #f1f1f1;
            min-height: 100vh;
        }

        /* --- Global Layout Styles --- */
        .sidebar {
            position: fixed; left: 0; top: 0;
            width: 260px; height: 100vh;
            background: #0C0C0C; color: white;
            padding: 20px; overflow-y: auto; z-index: 100;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .logo { margin-bottom: 40px; display: flex; align-items: center; justify-content: center; }
        .logo img { max-width: 180px; height: auto; }

        .nav-item {
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.2s;
            text-decoration: none;
            color: white;
        }
        .nav-item:hover {
            background: #1A1A1A;
            color: #C8FF00;
        }
        .nav-item.active {
            background: #C8FF00;
            color: #0C0C0C;
        }
        .nav-icon {
            width: 20px;
            height: 20px;
        }

        .main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
        
        .dashboard-header {
            background: #0C0C0C; padding: 20px 40px; border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 50;
        }

        .vendor-info { display: flex; align-items: center; gap: 15px; }
        .vendor-name-text { font-size: 16px; font-weight: 700; color: #f1f1f1; }
        .profile-photo-wrapper {
            width: 44px; height: 44px; border-radius: 50%;
            background: #1A1A1A; display: flex; align-items: center; justify-content: center;
            border: 2px solid #C8FF00; color: #C8FF00; font-weight: 800;
        }

        .action-icons { display: flex; gap: 20px; }
        .action-icon { width: 24px; height: 24px; color: #64748b; cursor: pointer; transition: color 0.2s; }
        .action-icon:hover { color: #C8FF00; }

        .page-content-wrapper { padding: 40px; flex: 1; display: flex; flex-direction: column; gap: 30px; }

        /* CRM Header Stats */
        .crm-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .stat-card {
            background: #151515;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 20px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .stat-icon {
            width: 60px; height: 60px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
        }
        
        .stat-icon.blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .stat-icon.green { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .stat-icon.purple { background: rgba(168, 85, 247, 0.1); color: #a855f7; }

        .stat-details h3 { font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; margin-bottom: 5px; }
        .stat-details .value { font-size: 28px; font-weight: 900; color: #C8FF00; line-height: 1; }

        /* Customer Table Container */
        .crm-container {
            background: #151515;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .crm-toolbar {
            padding: 25px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #111111;
        }

        .crm-toolbar h2 { font-size: 18px; font-weight: 800; color: #f1f1f1; }

        .search-container { position: relative; width: 350px; }
        .search-input {
            width: 100%; padding: 12px 16px 12px 45px;
            border: 2px solid #2d3748; border-radius: 12px;
            background: #0C0C0C; color: #f1f1f1;
            font-size: 14px; font-weight: 500; transition: all 0.2s;
            outline: none;
        }
        .search-input:focus { border-color: #C8FF00; box-shadow: 0 0 0 4px rgba(200, 255, 0, 0.1); }
        .search-icon-inside {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 20px; height: 20px; color: #94a3b8;
        }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th {
            background: #111111; padding: 20px 30px;
            font-size: 12px; font-weight: 800; color: #64748b;
            text-transform: uppercase; letter-spacing: 1px;
            border-bottom: 2px solid #2d3748;
        }
        td { padding: 20px 30px; border-bottom: 1px solid #2d3748; vertical-align: middle; color: #f1f1f1; }
        tr:hover td { background: #1A1A1A; }
        tr:last-child td { border-bottom: none; }

        /* CRM Features Styles */
        .customer-profile { display: flex; align-items: center; gap: 15px; }
        .avatar {
            width: 44px; height: 44px; border-radius: 12px;
            background: #1A1A1A; display: flex; align-items: center; justify-content: center;
            font-weight: 800; color: #C8FF00; font-size: 16px; border: 1px solid rgba(200, 255, 0, 0.1);
        }
        .info-name { font-weight: 700; color: #f1f1f1; font-size: 15px; display: block; margin-bottom: 2px; }
        .info-email { font-size: 13px; color: #64748b; font-weight: 500; }

        .metric-block { display: flex; flex-direction: column; gap: 4px; }
        .metric-val { font-size: 16px; font-weight: 800; color: #C8FF00; }
        .metric-sub { font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; }

        .badge {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 6px 12px; border-radius: 100px;
            font-size: 12px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;
        }
        .badge.vip { background: #fef08a; color: #854d0e; }
        .badge.regular { background: #f1f5f9; color: #475569; }
        .badge.new { background: #dcfce3; color: #166534; }
        
        .status-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
        .status-active .status-dot { background: #22c55e; }
        .status-dormant .status-dot { background: #cbd5e1; }
        .status-text { font-size: 13px; font-weight: 600; color: #475569; }

        .action-group { display: flex; gap: 10px; }
        .btn-action {
            width: 36px; height: 36px; border-radius: 10px; border: none;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.2s; color: white;
            text-decoration: none;
        }
        .btn-wa { background: #25D366; }
        .btn-wa:hover { background: #1da851; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3); }
        
        .btn-email { background: #3b82f6; }
        .btn-email:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); }

        .empty-crm { padding: 60px 20px; text-align: center; }
        .empty-icon { font-size: 48px; margin-bottom: 20px; color: #94a3b8; }
        .empty-msg { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .empty-sub { font-size: 14px; color: #64748b; }

    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="../images/logo_c.png" alt="PreOrda">
        </div>
        <a href="dashboard.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span>Overview</span>
        </a>
        <a href="products.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <span>Products</span>
        </a>
        <a href="orders.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <span>Orders</span>
        </a>
        <a href="customers.php" class="nav-item active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <span>Customers</span>
        </a>
        <a href="brandcatmgt.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
            <span>Brands & Categories</span>
        </a>
        <a href="chat.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
            <span>Messages</span>
        </a>
        <a href="settings.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>Settings</span>
        </a>
        
        <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid #2d3748;">
            <a href="../actions/logout.php" class="nav-item" style="color: #fc8181;">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="dashboard-header">
            <div class="vendor-info">
                <div class="profile-photo-wrapper"><?php echo $initials; ?></div>
                <span class="vendor-name-text"><?php echo htmlspecialchars($business_name); ?></span>
            </div>
        </div>
        
        <div class="page-content-wrapper">
            
            <!-- CRM Metrics Panels -->
            <div class="crm-stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">👥</div>
                    <div class="stat-details">
                        <h3>Total Audience</h3>
                        <div class="value"><?php echo number_format($total_unique_customers); ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">💰</div>
                    <div class="stat-details">
                        <h3>Lifetime CRM Value</h3>
                        <div class="value">GH₵ <?php echo number_format($total_lifetime_revenue, 2); ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">⭐</div>
                    <div class="stat-details">
                        <h3>VIP Members</h3>
                        <div class="value"><?php echo number_format($total_vip_customers); ?></div>
                    </div>
                </div>
            </div>

            <!-- CRM Customer List -->
            <div class="crm-container">
                <div class="crm-toolbar">
                    <h2>Customer Database</h2>
                    <div class="search-container">
                        <svg class="search-icon-inside" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" class="search-input" id="searchInput" placeholder="Search customer CRM...">
                    </div>
                </div>

                <?php if (empty($customers)): ?>
                    <div class="empty-crm">
                        <div class="empty-icon">📂</div>
                        <div class="empty-msg">No Customers Yet</div>
                        <div class="empty-sub">Once orders start rolling in, your audience metrics will appear here.</div>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Profile</th>
                                <th>Segment</th>
                                <th>Activity Status</th>
                                <th>Lifetime Value</th>
                                <th>Direct Reach</th>
                            </tr>
                        </thead>
                        <tbody id="customerTableBody">
                            <?php foreach ($customers as $c): 
                                // Feature: Segmentation Badge
                                $is_vip = ($c['total_orders'] >= 3 || $c['lifetime_value'] >= 1000);
                                $is_new = ($c['total_orders'] == 1);
                                
                                $badge_class = 'regular';
                                $badge_text = 'Regular';
                                if ($is_vip) { $badge_class = 'vip'; $badge_text = 'VIP Status'; }
                                elseif ($is_new) { $badge_class = 'new'; $badge_text = 'New Buyer'; }

                                // Feature: Activity Tracking
                                $last_order = new DateTime($c['last_order_date']);
                                $now = new DateTime();
                                $days_since = $now->diff($last_order)->days;
                                $status_class = ($days_since <= 30) ? 'status-active' : 'status-dormant';
                                $status_text = ($days_since <= 30) ? 'Active' : 'Dormant';

                                // Initials for Avatar
                                $c_initials = strtoupper(substr($c['customer_name'], 0, 1) . substr(strrchr($c['customer_name'], ' '), 1, 1));
                                if (empty(trim(strrchr($c['customer_name'], ' ')))) $c_initials = strtoupper(substr($c['customer_name'], 0, 2));
                                
                                // Phone normalization for WhatsApp
                                $phone = preg_replace('/[^0-9]/', '', $c['customer_phone']);
                                if (substr($phone, 0, 1) == '0') $phone = '233' . substr($phone, 1);
                            ?>
                            <tr class="customer-row">
                                <td>
                                    <div class="customer-profile">
                                        <div class="avatar"><?php echo $c_initials; ?></div>
                                        <div>
                                            <span class="info-name c-name"><?php echo htmlspecialchars($c['customer_name']); ?></span>
                                            <span class="info-email c-email"><?php echo htmlspecialchars($c['customer_email']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                                </td>
                                <td>
                                    <div class="metric-block">
                                        <div class="<?php echo $status_class; ?>">
                                            <span class="status-dot"></span><span class="status-text"><?php echo $status_text; ?></span>
                                        </div>
                                        <div class="metric-sub">Last: <?php echo date('M j, Y', strtotime($c['last_order_date'])); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="metric-block">
                                        <span class="metric-val">GH₵ <?php echo number_format($c['lifetime_value'], 2); ?></span>
                                        <span class="metric-sub"><?php echo $c['total_orders']; ?> Lifetime Orders</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="mailto:<?php echo htmlspecialchars($c['customer_email']); ?>" class="btn-action btn-email" title="Send Email">
                                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        </a>
                                        <?php if (!empty($phone)): ?>
                                        <a href="https://wa.me/<?php echo $phone; ?>?text=Hello <?php echo htmlspecialchars($c['customer_name']); ?>, this is <?php echo htmlspecialchars($business_name); ?>" target="_blank" class="btn-action btn-wa" title="WhatsApp Direct">
                                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 21c-1.566-.002-3.111-.39-4.502-1.127l-4.99 1.309 1.332-4.86A9.972 9.972 0 0 1 2.5 12C2.5 6.486 6.986 2 12.5 2s10 4.486 10 10-4.486 10-10.469 10zm-4.706-2.583a8.216 8.216 0 0 0 4.706 1.442c4.437 0 8.04-3.601 8.04-8.037 0-4.437-3.603-8.04-8.04-8.04-4.437 0-8.04 3.603-8.04 8.04 0 1.637.5 3.197 1.444 4.54l-.841 3.067 3.137-.822h-.006zm4.646-13.67c4.225 0 7.662 3.435 7.662 7.66 0 4.225-3.437 7.66-7.662 7.66-1.503 0-2.964-.438-4.223-1.267l-2.618.686.698-2.553a7.568 7.568 0 0 1-1.31-4.321c0-4.225 3.437-7.661 7.663-7.661l-.21.196zm-2.071 2.827c-.201-.005-.403.045-.591.144-.082.046-.299.191-.299.191-1.17 1.15-.3 3.551 1.706 6.136 1.933 2.502 4.45 3.57 5.645 2.545 0 0 .157-.208.204-.286.104-.183.155-.386.155-.589l-.265-2.031-2.067-.557c-.287-.078-.59.049-.757.291-.013.018-.179.3-.179.3s-.292.016-.62-.178a9.406 9.406 0 0 1-2.62-2.31c-.131-.22-.041-.351-.041-.351s.141-.219.16-.234c.2-.204.288-.49.231-.767l-1.022-2.339z"/></svg>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Client-side filtering script -->
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('.customer-row');
            
            rows.forEach(row => {
                let name = row.querySelector('.c-name').textContent.toLowerCase();
                let email = row.querySelector('.c-email').textContent.toLowerCase();
                if (name.includes(filter) || email.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>