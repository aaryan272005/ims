(function () {
  function normalizedText(el) {
    return (el.getAttribute("aria-label") || el.textContent || "")
      .replace(/\s+/g, " ")
      .trim()
      .toLowerCase();
  }

  function setTitle(el, title) {
    if (!(el instanceof Element) || !title) return;
    if (!el.getAttribute("title")) el.setAttribute("title", title);
  }

  function applyDashboardCardTitles(root) {
    root.querySelectorAll(".dashboardCard").forEach((card) => {
      const label = normalizedText(card.querySelector("p") || card);
      if (label === "total products") setTitle(card, "Total products in inventory");
      else if (label === "total suppliers") setTitle(card, "Total registered suppliers");
      else if (label === "total users") setTitle(card, "Total system users");
      else if (label === "total orders") setTitle(card, "Total purchase orders");
    });
  }

  function applyFormTitles(root) {
    root.querySelectorAll("input[type='email'], input[name='email'], input[name='username'], input[name='notify_email']").forEach((el) => setTitle(el, "Enter valid email"));
    root.querySelectorAll("input[type='password']").forEach((el) => setTitle(el, "Enter your password"));
    root.querySelectorAll("input[name='product_name'], #swal_product_name").forEach((el) => setTitle(el, "Enter product name"));
    root.querySelectorAll("select.product_name").forEach((el) => setTitle(el, "Select product"));
    root.querySelectorAll("#supplierInput, select[name='suppliers[]']").forEach((el) => setTitle(el, "Select supplier"));
    root.querySelectorAll("select[name='role']").forEach((el) => setTitle(el, "Select user access level"));
    root.querySelectorAll("#qtyDelivered, input[name='quantity[]'], input[name^='quantity[']").forEach((el) => setTitle(el, "Enter quantity"));
  }

  function applyButtonTitles(root) {
    root.querySelectorAll("#toggleBtn").forEach((el) => setTitle(el, "Toggle sidebar"));
    root.querySelectorAll(".logoutBtn").forEach((el) => setTitle(el, "Log out of the system"));
    root.querySelectorAll(".viewDeliveryBtn").forEach((el) => setTitle(el, "View delivery history"));
    root.querySelectorAll(".checkoutBtn").forEach((el) => setTitle(el, "Proceed to checkout"));
    root.querySelectorAll(".btn-report").forEach((el) => {
      const text = normalizedText(el);
      if (text.includes("excel")) setTitle(el, "Download this report as Excel");
      else if (text.includes("pdf")) setTitle(el, "Download this report as PDF");
    });
    root.querySelectorAll(".productCard").forEach((el) => setTitle(el, el.classList.contains("out") ? "Product unavailable" : "Add this item"));
    root.querySelectorAll(".qtyBtn").forEach((el) => setTitle(el, normalizedText(el) === "-" ? "Decrease quantity" : "Increase quantity"));
    root.querySelectorAll(".removeBtn, .removeProductRowBtn, .removeProduct").forEach((el) => setTitle(el, "Remove this item"));
    root.querySelectorAll("button, a.action-btn").forEach((el) => {
      const text = normalizedText(el);
      if (el.matches(".deleteBtn, .deleteOrderBtn") || text.includes("delete")) setTitle(el, "Delete this item");
      else if (el.matches(".editBtn") || text.includes("edit")) setTitle(el, "Edit this item");
      else if (text.includes("update")) setTitle(el, "Update this item");
      else if (text.includes("add") || text.includes("create")) setTitle(el, "Add new item");
      else if (el.getAttribute("type") === "submit" || text.includes("submit") || text.includes("login") || text.includes("notify") || text.includes("send")) setTitle(el, "Submit form");
    });
  }

  function applyStatusTitles(root) {
    root.querySelectorAll(".status").forEach((el) => setTitle(el, "Order status"));
    root.querySelectorAll(".stockText").forEach((el) => setTitle(el, "Product stock status"));
    root.querySelectorAll("span, div, td").forEach((el) => {
      const text = normalizedText(el);
      if (text === "pending" || text === "incomplete" || text === "complete") setTitle(el, "Order status");
      else if (text === "queued" || text === "sent" || text === "partial" || text === "failed") setTitle(el, "Campaign status");
      else if (text === "out of stock" || text === "low stock" || text === "in stock" || text.startsWith("low stock (") || text.startsWith("in stock (")) setTitle(el, "Product stock status");
    });
  }

  function applySiteTooltips(root) {
    if (!(root instanceof Element) && root !== document) return;
    applyDashboardCardTitles(root);
    applyFormTitles(root);
    applyButtonTitles(root);
    applyStatusTitles(root);
  }

  window.applySiteTooltips = applySiteTooltips;

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      applySiteTooltips(document);
    });
  } else {
    applySiteTooltips(document);
  }
})();
