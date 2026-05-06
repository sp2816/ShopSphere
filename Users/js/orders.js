document.addEventListener("DOMContentLoaded", function () {
  const orderCards = document.querySelectorAll(".order-card");

  orderCards.forEach(card => {
    const button = card.querySelector(".btn");
    const statusSpan = card.querySelector(".status");
    const productName = card.querySelector("h3").textContent;
    const orderId = card.querySelector("p:nth-of-type(2)").textContent;

    if (button.classList.contains("cancel")) {
      // Cancel Order functionality
      button.addEventListener("click", () => {
        const confirmCancel = confirm(`Are you sure you want to cancel the order for "${productName}"?`);
        if (confirmCancel) {
          statusSpan.textContent = "Cancelled";
          statusSpan.className = "status cancelled";
          button.disabled = true;
          button.textContent = "Order Cancelled";
        }
      });
    } else {
      // View Details or Track Order
      button.addEventListener("click", () => {
        alert(`Order Details:\n${productName}\n${orderId}\nStatus: ${statusSpan.textContent}`);
      });
    }
  });
});
