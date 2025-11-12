// ...existing code...
// Helper: Validate brand name (string, not empty, allow basic punctuation)
function validateBrandName(name) {
  return typeof name === 'string' && /^[a-zA-Z0-9 &,_\-\']+$/.test(name) && name.trim().length > 0;
}

// AJAX helper (POST JSON)
function ajaxAction(url, data, callback) {
  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(res => res.json())
  .then(callback)
  .catch(err => {
    console.error('AJAX Error:', err);
    alert('Network error. Please try again.');
  });
}

// Fetch brands and render into #brandsContainer
function fetchAndRenderBrands() {
  const container = document.getElementById('brandsContainer');
  if (!container) return;
  container.innerHTML = 'Loading...';

  fetch('../actions/fetch_brand_action.php')
    .then(res => res.json())
    .then(json => {
      if (!json.success) {
        container.innerHTML = '<div class="error">Failed to load brands.</div>';
        return;
      }
      renderBrands(json.data || []);
    })
    .catch(err => {
      console.error(err);
      container.innerHTML = '<div class="error">Failed to load brands.</div>';
    });
}

function renderBrands(brands) {
  const container = document.getElementById('brandsContainer');
  if (!container) return;

  if (!brands.length) {
    container.innerHTML = '<div>No brands yet.</div>';
    return;
  }

  container.innerHTML = ''; // clear

  brands.forEach(b => {
    // prefer fields: brand_id, brand_name, cat_id or cat_id_user_id
    const id = b.brand_id ?? b.id ?? b.brandId ?? b.id;
    const name = b.brand_name ?? b.name ?? '';
    const cat = b.category_name ?? b.cat_id_user_id ?? b.category ?? '';

    const card = document.createElement('div');
    card.className = 'brand-card';
    card.dataset.id = id;

    card.innerHTML = `
      <div>
        <h4>${escapeHtml(name)}</h4>
        <div style="font-size:0.9rem;color:#555">Category: ${escapeHtml(String(cat))}</div>
      </div>
      <div class="brand-actions">
        <button type="button" class="edit-btn" data-id="${escapeAttr(id)}">Edit</button>
        <button type="button" class="delete-btn" data-id="${escapeAttr(id)}">Delete</button>
      </div>
    `;
    container.appendChild(card);
  });
}

// escape helpers
function escapeHtml(s) {
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
function escapeAttr(s) { return escapeHtml(s); }

// handle clicks (edit / delete) via event delegation
document.addEventListener('click', function (e) {
  const editBtn = e.target.closest('.edit-btn');
  if (editBtn) {
    const id = editBtn.dataset.id;
    openEditModal(id);
    return;
  }

  const delBtn = e.target.closest('.delete-btn');
  if (delBtn) {
    const id = delBtn.dataset.id;
    if (confirm('Delete this brand?')) deleteBrand(id);
    return;
  }
});

// Edit modal logic (uses markup in admin/brand.php)
function openEditModal(id) {
  const card = document.querySelector(`.brand-card[data-id="${CSS.escape(id)}"]`);
  if (!card) return;
  const name = card.querySelector('h4')?.textContent ?? '';

  const modal = document.getElementById('editModal');
  const form = document.getElementById('editBrandForm');
  const inputId = document.getElementById('edit_brand_id');
  const inputName = document.getElementById('edit_brand_name');

  if (!modal || !form || !inputId || !inputName) return;

  inputId.value = id;
  inputName.value = name;
  modal.classList.add('active');

  // ensure we don't attach multiple handlers
  form.onsubmit = function (ev) {
    ev.preventDefault();
    const newName = inputName.value.trim();
    if (!validateBrandName(newName)) {
      alert('Invalid brand name.');
      return;
    }
    updateBrand(id, newName, function(success) {
      if (success) {
        modal.classList.remove('active');
        fetchAndRenderBrands();
      }
    });
  };
}

// cancel edit
document.getElementById && document.getElementById('cancelEdit')?.addEventListener('click', function () {
  document.getElementById('editModal')?.classList.remove('active');
});

// updateBrand uses ajaxAction; accepts optional callback(success)
function updateBrand(id, new_name, cb) {
  ajaxAction('../actions/update_brand_action.php', { id, new_name }, function(res) {
    if (res && res.success) {
      alert('Brand updated!');
      if (typeof cb === 'function') cb(true);
    } else {
      alert('Error: ' + (res?.message ?? 'Failed to update'));
      if (typeof cb === 'function') cb(false);
    }
  });
}

// deleteBrand uses ajaxAction; on success remove card
function deleteBrand(id) {
  ajaxAction('../actions/delete_brand_action.php', { id }, function(res) {
    if (res && res.success) {
      alert('Brand deleted!');
      const card = document.querySelector(`.brand-card[data-id="${CSS.escape(id)}"]`);
      if (card) card.remove();
    } else {
      alert('Error: ' + (res?.message ?? 'Failed to delete'));
    }
  });
}

// hook create form (existing form id="addBrandForm")
document.addEventListener("DOMContentLoaded", () => {
  // initialize list
  fetchAndRenderBrands();

  const form = document.getElementById("addBrandForm");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    const nameEl = this.querySelector("#brand_name") || this.querySelector("input[name='brand_name']");
    const catEl = this.querySelector("#cat_id") || this.querySelector("select[name='cat_id']") || this.querySelector("input[name='cat_id']");
    const name = nameEl ? nameEl.value.trim() : '';
    const cat_id = catEl ? catEl.value.trim() : '';

    if (!validateBrandName(name)) { alert('Invalid brand name.'); return; }
    if (!cat_id) { alert('Select category.'); return; }

    ajaxAction('../actions/add_brand_action.php', { name, cat_id }, function(res) {
      alert(res.success ? 'Brand added!' : 'Error: ' + (res.message || 'Failed'));
      if (res.success) {
        form.reset();
        fetchAndRenderBrands();
      }
    });
});
});