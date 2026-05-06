document.addEventListener("DOMContentLoaded", function () {
  const wishlistItems = document.querySelectorAll(".wishlist-item");

  wishlistItems.forEach(item => {
    const removeBtn = item.querySelector(".btn.remove");
    const moveToCartBtn = item.querySelector(".btn.cart");

    // Remove from Wishlist
    removeBtn.addEventListener("click", () => {
      const confirmDelete = confirm("Remove this item from wishlist?");
      if (confirmDelete) {
        item.remove();
        alert("Item removed from wishlist.");
      }
    });

    // Move to Cart
    moveToCartBtn.addEventListener("click", () => {
      alert("Item moved to cart!");
      item.remove(); // Remove from wishlist
      // Optionally, you could also store it to cart using localStorage or a server API
    });
  });
});
