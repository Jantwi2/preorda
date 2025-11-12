document.addEventListener("DOMContentLoaded", () => {

  // Update quantity
  document.querySelectorAll(".update-btn").forEach(button => {
    button.addEventListener("click", () => {
      const productId = button.dataset.productId;
      const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
      const quantity = input ? input.value : 1;

      fetch("../actions/update_cart_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `product_id=${productId}&quantity=${quantity}`
      })
      .then(res => res.text())
      .then(() => location.reload());
    });
  });

  // Remove item
  document.querySelectorAll(".remove-btn").forEach(button => {
    button.addEventListener("click", () => {
      const productId = button.dataset.productId;

      fetch("../actions/remove_from_cart_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `product_id=${productId}`
      })
      .then(res => res.text())
      .then(() => location.reload());
    });
  });

  // Empty cart
  const emptyBtn = document.getElementById("empty-cart");
  if (emptyBtn) {
    emptyBtn.addEventListener("click", () => {
      if (confirm("Are you sure you want to empty your cart?")) {
        fetch("../actions/empty_cart_action.php", { method: "POST" })
        .then(res => res.text())
        .then(() => location.reload());
      }
    });
  }

});
