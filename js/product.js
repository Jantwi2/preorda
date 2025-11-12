// ...existing code...
(() => {
  // Helpers
  function qs(sel, ctx = document) { return ctx.querySelector(sel); }
  function qsa(sel, ctx = document) { return Array.from(ctx.querySelectorAll(sel)); }
  function showMsg(msg) { alert(msg); }
  function safeText(s){ return (s===null||s===undefined)?'':String(s); }

  // Filter brands dropdown based on selected category (brands have data-cat attribute)
  function filterBrandsByCategory() {
    const cat = qs('#cat_id')?.value;
    const brandSelect = qs('#brand_id');
    if (!brandSelect) return;
    qsa('option', brandSelect).forEach(opt => {
      const optCat = opt.dataset.cat ?? '';
      if (!opt.value) { // placeholder
        opt.hidden = false;
        opt.disabled = false;
        return;
      }
      if (!optCat || !cat) {
        opt.hidden = false;
        opt.disabled = false;
      } else {
        const show = String(optCat) === String(cat);
        opt.hidden = !show;
        opt.disabled = !show;
      }
    });
    // if currently selected option hidden, reset to placeholder
    if (brandSelect.selectedOptions.length && brandSelect.selectedOptions[0].hidden) brandSelect.value = '';
  }

  // Fetch and render products
  async function fetchProducts() {
    const container = qs('#productsContainer');
    if (!container) return;
    container.textContent = 'Loading...';
    try {
      const res = await fetch('../actions/fetch_product_action.php', { credentials: 'same-origin' });
      const json = await res.json();
      if (!json.success) {
        container.innerHTML = `<div class="error">${safeText(json.message || 'Failed to load products.')}</div>`;
        return;
      }
      renderProducts(json.data || []);
    } catch (err) {
      console.error(err);
      container.innerHTML = '<div class="error">Failed to load products.</div>';
    }
  }

  function renderProducts(items) {
    const container = qs('#productsContainer');
    if (!container) return;
    container.innerHTML = '';
    if (!items.length) {
      container.innerHTML = '<div>No products yet.</div>';
      return;
    }
    items.forEach(p => {
      const id = p.product_id ?? p.id ?? '';
      const title = safeText(p.product_title ?? p.title);
      const price = safeText(p.product_price ?? p.price);
      const cat = safeText(p.category_name ?? p.cat_name ?? p.cat_id);
      const brand = safeText(p.brand_name ?? p.brand_name);
      const img = p.product_image ? (`../uploads/${p.product_image}`) : ('/assets/images/placeholder.png');

      const tile = document.createElement('div');
      tile.className = 'product-tile';
      tile.dataset.id = id;
      tile.innerHTML = `
        <div style="flex:0 0 auto;"><img src="${img}" alt="${title}"</div>
        <div style="flex:1 1 auto;"><strong>${title}</strong><div style="color:#666">${cat} — ${brand}</div></div>
        <div><strong>GHS ${price}</strong></div>
        <div style="display:flex;gap:0.5rem;margin-top:0.5rem;">
          <button class="btn editBtn" data-id="${id}">Edit</button>
          <button class="btn ghost deleteBtn" data-id="${id}">Delete</button>
        </div>
      `;
      container.appendChild(tile);
    });
  }

  // Load product data into form for editing
  async function loadProductIntoForm(id) {
    try {
      const res = await fetch('../actions/get_product_action.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id}),
        credentials: 'same-origin'
      });
      const json = await res.json();
      if (!json.success) { showMsg(json.message || 'Failed to load product'); return; }
      const p = json.data;
      qs('#product_id').value = p.product_id ?? '';
      qs('#title').value = p.product_title ?? '';
      qs('#price').value = p.product_price ?? '';
      qs('#description').value = p.product_description ?? '';
      qs('#keyword').value = p.product_keyword ?? '';
      qs('#cat_id').value = p.cat_id ?? '';
      filterBrandsByCategory();
      qs('#brand_id').value = p.brand_id ?? '';
      // set image_path hidden
      qs('#image_path').value = p.product_image ? `../uploads/${p.product_image}` : '';
      window.scrollTo({top:0,behavior:'smooth'});
    } catch (err) {
      console.error(err);
      showMsg('Failed to load product for edit.');
    }
  }

  // Delete product
  async function deleteProduct(id, tileEl) {
    if (!confirm('Delete this product?')) return;
    try {
      const res = await fetch('../actions/delete_product_action.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id}),
        credentials: 'same-origin'
      });
      const json = await res.json();
      if (json.success) {
        showMsg('Product deleted');
        if (tileEl) tileEl.remove();
      } else {
        showMsg(json.message || 'Failed to delete product');
      }
    } catch (err) {
      console.error(err);
      showMsg('Failed to delete product');
    }
  }

  // Submit form (add or edit)
  async function submitProductForm(ev) {
    ev.preventDefault();
    const form = ev.target;
    const fd = new FormData(form);
    // map input names expected by server
    // server expects product_image or images[]; our file input is #image (name=image) and images[] (name=images[])
    // normalize names:
    if (fd.has('image')) {
      const file = fd.get('image');
      if (file && file.size) fd.set('product_image', file);
      fd.delete('image');
    }
    // append images[] if present (FormData preserves them)
    // ensure title/price names match expected
    if (!fd.get('title') && fd.get('product_title')) fd.set('title', fd.get('product_title'));
    try {
      const saveBtn = qs('#saveBtn');
      if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Saving...'; }
      const res = await fetch('../actions/add_product_action.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });
      const json = await res.json();
      if (json.success) {
        showMsg(json.message || 'Saved');
        form.reset();
        // clear hidden paths
        qs('#image_path').value = '';
        qs('#image_paths').value = '';
        await fetchProducts();
      } else {
        showMsg(json.message || 'Failed to save product');
      }
    } catch (err) {
      console.error(err);
      showMsg('Network error while saving product');
    } finally {
      if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Save Product'; }
    }
  }

  // Event delegation for edit/delete buttons
  function attachTileHandlers() {
    document.addEventListener('click', function(e) {
      const edit = e.target.closest('.editBtn');
      if (edit) {
        const id = edit.dataset.id;
        const tile = edit.closest('.product-tile');
        loadProductIntoForm(id);
        return;
      }
      const del = e.target.closest('.deleteBtn');
      if (del) {
        const id = del.dataset.id;
        const tile = del.closest('.product-tile');
        deleteProduct(id, tile);
        return;
      }
    });
  }

  // init
  document.addEventListener('DOMContentLoaded', () => {
    filterBrandsByCategory();
    qs('#cat_id')?.addEventListener('change', filterBrandsByCategory);

    const form = qs('#productForm');
    if (form) form.addEventListener('submit', submitProductForm);

    attachTileHandlers();
    fetchProducts();
  });

})();