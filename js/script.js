function reOrderTable(tableSelector, countSelector, label) {
  let rows = $(tableSelector + " tbody tr");

  // reorder numbers
  rows.each(function (index) {
    $(this)
      .find("td:first")
      .text(index + 1);
  });

  // update count text
  if (countSelector) {
    $(countSelector).text(rows.length + " " + label);
  }
}

const SCRIPT_APP_CSRF_TOKEN = (window.appConfig && window.appConfig.csrfToken) || "";

let message = $(".responseMessage");

if (message.length) {
  // Fade IN
  setTimeout(function () {
    message.addClass("show");
  }, 100);

  // Fade OUT after 3 seconds
  setTimeout(function () {
    message.removeClass("show").addClass("hide");

    setTimeout(function () {
      message.remove();
    }, 500);
  }, 3000);
}

$(document).on("click", ".deleteUser", function (e) {
  e.preventDefault();

  let button = $(this);
  let userId = button.data("userid");
  let fname = button.data("fname");
  let lname = button.data("lname");
  let fullName = fname + " " + lname;

  Swal.fire({
    title: "Delete User?",
    text: "Are you sure you want to remove " + fullName + "?",
    icon: "warning",
    showCancelButton: true,
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        method: "POST",
        url: "database/delete.php",

        data: { 
          id: userId, 
          table: "users",
          csrf_token: SCRIPT_APP_CSRF_TOKEN
        },

        dataType: "json",

        success: function (response) {
          console.log(response); // DEBUG

          if (response.success) {
            button.closest("tr").fadeOut(300, function () {
              $(this).remove();

              reOrderTable(".users", ".userCount", "Users");
            });

            Swal.fire({
              icon: "success",
              title: "Deleted!",
              text: fullName + " has been removed.",
              timer: 2000,
              showConfirmButton: false,
            });

          } else {
            Swal.fire("Error", response.message, "error");
          }
        },
      });
    }
  });
});

// Edit logic
$(document).on("click", ".editUser", function (e) {
  e.preventDefault();

  let button = $(this);

  let userId = button.data("userid");
  let fname = button.data("fname");
  let lname = button.data("lname");
  let email = button.data("email");

  Swal.fire({
    title: "Edit User",
    html: `<input id="swal_fname" class="swal2-input" placeholder="First Name" value="${fname}">
             <input id="swal_lname" class="swal2-input" placeholder="Last Name" value="${lname}">
             <input id="swal_email" class="swal2-input" placeholder="Email" value="${email}">`,
    confirmButtonText: "Update",
    confirmButtonColor: "#008cff",
    focusConfirm: false,
    preConfirm: () => {
      return {
        first_name: document.getElementById("swal_fname").value,
        last_name: document.getElementById("swal_lname").value,
        email: document.getElementById("swal_email").value,
      };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        method: "POST",
        url: "database/update.php",
        data: {
          table: "users",
          user_id: userId,
          first_name: result.value.first_name,
          last_name: result.value.last_name,
          email: result.value.email,
          csrf_token: SCRIPT_APP_CSRF_TOKEN,
        },
        dataType: "json",

        success: function (response) {
          if (response.success) {
            let row = button.closest("tr");

            row.find(".fname").text(result.value.first_name);
            row.find(".lname").text(result.value.last_name);
            row.find(".email").text(result.value.email);

            // IMPORTANT: update data attributes
            button.attr("data-fname", result.value.first_name);
            button.attr("data-lname", result.value.last_name);
            button.attr("data-email", result.value.email);

            Swal.fire({
              icon: "success",
              title: "Updated!",
              text: "User updated successfully.",
              confirmButtonColor: "#008cff",
              timer: 2000,
              showConfirmButton: false,
            });
          } else {
            Swal.fire("Error", response.message, "error");
          }
        },
      });
    }
  });
});

$(document).on("click", ".deleteProduct", function (e) {
  e.preventDefault();

  let button = $(this);
  let productId = button.data("id");
  let productName = button.data("name");

  Swal.fire({
    title: "Delete Product?",
    text: "Are you sure you want to remove " + productName + "?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#008cff",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Yes, Delete",
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        method: "POST",
        url: "database/delete.php",
        data: { id: productId, table: "products", csrf_token: SCRIPT_APP_CSRF_TOKEN },
        dataType: "json",

        success: function (response) {
          if (response.success) {
            button.closest("tr").fadeOut(300, function () {
              $(this).remove();
              reOrderTable(".products", ".productCount", "Products"); // reorder numbering
            });

            let count = $(".users tbody tr").length;
            $(".userCount").text(count + " Products");

            Swal.fire({
              icon: "success",
              title: "Deleted!",
              text: productName + " has been removed.",
              confirmButtonColor: "#008cff",
              timer: 2000,
              showConfirmButton: false,
            });
          } else {
            Swal.fire("Error", response.message, "error");
          }
        },
      });
    }
  });
});

// edit Product Details
$(document).on("click", ".editProduct", function (e) {
  e.preventDefault();

  let button = $(this);

  let productId = button.data("pid");
  let productName = button.data("name");
  let description = button.data("description");

  Swal.fire({
    title: "Edit Product",
    html: `<input id="swal_product_name" class="swal2-input" placeholder="Product Name" value="${productName}">
        <textarea id="swal_description" class="swal2-textarea" placeholder="Description">${description}</textarea>
        <input type="file" id="swal_image" class="swal2-file">`,
    confirmButtonText: "Update",
    confirmButtonColor: "#008cff",
    focusConfirm: false,
    preConfirm: () => {
      let formData = new FormData();

      formData.append("table", "products");
      formData.append("id", productId);
      formData.append(
        "product_name",
        document.getElementById("swal_product_name").value,
      );
      formData.append(
        "description",
        document.getElementById("swal_description").value,
      );

      let file = document.getElementById("swal_image").files[0];
      formData.append("csrf_token", SCRIPT_APP_CSRF_TOKEN);
      if (file) {
        formData.append("image", file);
      }

      return formData;
    },
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        method: "POST",
        url: "database/update.php",
        data: result.value,
        processData: false,
        contentType: false,
        dataType: "json",

        success: function (response) {
          if (response.success) {
            let row = button.closest("tr");

            row.find(".product_name").text(response.product_name);
            row.find(".description").text(response.description);

            if (response.img) {
              row
                .find(".productImages")
                .attr("src", "uploads/products/" + response.img);
            }

            Swal.fire({
              icon: "success",
              title: "Updated!",
              text: "Product updated successfully.",
              timer: 2000,
              showConfirmButton: false,
            });
          }
        },
      });
    }
  });
});

// Edit supplier

$(document).on("click", ".editSupplier", function (e) {
  e.preventDefault();

  let button = $(this);

  let supplierId = button.data("id");
  let name = button.data("name");
  let location = button.data("location");
  let email = button.data("email");

  Swal.fire({
    title: "Update Supplier",

    html: `
        <input id="swal_name" class="swal2-input" placeholder="Supplier Name" value="${name}">
        <input id="swal_location" class="swal2-input" placeholder="Location" value="${location}">
        <input id="swal_email" class="swal2-input" placeholder="Email" value="${email}">
        `,

    confirmButtonText: "Update",
    confirmButtonColor: "#008cff",

    preConfirm: () => {
      return {
        supplier_name: $("#swal_name").val(),
        supplier_location: $("#swal_location").val(),
        email: $("#swal_email").val(),
      };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "database/update.php",
        method: "POST",

        data: {
          table: "supplier",
          supplier_id: supplierId,
          supplier_name: result.value.supplier_name,
          supplier_location: result.value.supplier_location,
          email: result.value.email,
          csrf_token: SCRIPT_APP_CSRF_TOKEN,
        },

        dataType: "json",

        success: function (response) {
          if (response.success) {
            let row = button.closest("tr");

            row.find(".supplierName").text(result.value.supplier_name);
            row.find(".supplierLocation").text(result.value.supplier_location);
            row.find(".supplierEmail").text(result.value.email);

            Swal.fire({
              icon: "success",
              title: "Updated!",
              timer: 2000,
              showConfirmButton: false,
            });
          } else {
            Swal.fire("Error", response.message, "error");
          }
        },
      });
    }
  });
});

$(document).on("click", ".deleteSupplier", function (e) {
  e.preventDefault();

  let button = $(this);

  let supplierId = button.data("id");
  let name = button.data("name");

  Swal.fire({
    title: "Delete Supplier?",
    text: "Are you sure you want to remove " + name + "?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#008cff",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Yes, Delete",
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        method: "POST",
        url: "database/delete.php",

        data: {
          id: supplierId,
          table: "supplier",
          csrf_token: SCRIPT_APP_CSRF_TOKEN,
        },

        dataType: "json",

        success: function (response) {
          console.log(response); // DEBUG

          if (response.success) {
            button.closest("tr").fadeOut(300, function () {
              $(this).remove();

              reOrderTable(".suppliers", ".supplierCount", "Suppliers");
            });

            Swal.fire({
              icon: "success",
              title: "Deleted!",
              text: name + " has been removed.",
              confirmButtonColor: "#008cff",
              timer: 2000,
              showConfirmButton: false,
            });
          } else {
            Swal.fire("Error", response.message, "error");
          }
        },
      });
    }
  });
});

$("#addProductBtn").click(function () {
  let row = `
<div class="orderRow">

<label>PRODUCT NAME</label>

<select class="productSelect" name="products[]">

<option value="">Select Product</option>
`;

  products.forEach(function (product) {
    row += `<option value="${product.id}">${product.product_name}</option>`;
  });

  row += `</select>

<div class="supplierContainer"></div>

<button type="button" class="removeProduct">Remove</button>

</div>`;

  $("#orderItems").append(row);
});

$(document).on("change", ".productSelect", function () {
  let productId = $(this).val();
  let container = $(this).closest(".orderRow").find(".supplierContainer");

  $.ajax({
    url: "database/get-product-suppliers.php",

    method: "POST",

    data: { product_id: productId, csrf_token: SCRIPT_APP_CSRF_TOKEN },

    success: function (response) {
      container.html(response);
    },
  });
});

$(document).on("click", ".removeProduct", function () {
  $(this).closest(".orderRow").remove();
});
