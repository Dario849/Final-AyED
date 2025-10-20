loadAllProducts();
$(".modal").on("hidden.bs.modal", function () {
  $(this).find("input, textarea").val("");
  loadAllProducts();
});
$("#addNewStockBtn").click(function (e) {
  const newStockProductId = $("#prodLabel").attr("data-prod");
  const newStockDate = $("#addStockDate").val();
  const newStockNumber = $("#addStockNumber").val();
  const newStockText = $("#addStockText").val();
  if (newStockDate && newStockNumber && newStockText) {
    let dataSet = {
      action: "add",
      prodId: newStockProductId,
      stockDate: newStockDate,
      stockNumber: newStockNumber,
      stockText: newStockText,
    };
    $.ajax({
      type: "POST",
      dataType: "json",
      url: "system/service/query.php",
      data: dataSet,
      success: function (response) {
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 5000,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
          },
          // didDestroy: (toast) => { //Si se cierra la notificación por tiempo: vacía los campos ingresados y cierra la ventana modal (exceptuando la fecha)
          //     $('#addStockNumber').val(0);
          //     $('#addStockText').val('');
          //     const modalInstance = bootstrap.Modal.getInstance($('#addStockModal')[0]);
          //     modalInstance.hide();
          // }
        });
        Toast.fire({
          icon: "success",
          title: response.message,
        });
      },
    });
    $("#addStockNumber").val("");
    $("#addStockText").val("");
    return;
  }
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
    },
  });
  Toast.fire({
    icon: "error",
    title: "Complete todos los campos!",
  });
});
$("#substractStockBtn").click(function (e) {
  const newStockProductId = $("#substrProdLabel").attr("data-prod");
  const newStockDate = $("#substrStockDate").val();
  const newStockNumber = $("#substrStockNumber").val();
  const newStockText = $("#substrStockText").val();
  if (newStockDate && newStockNumber && newStockText) {
    let dataSet = {
      action: "substract",
      prodId: newStockProductId,
      stockDate: newStockDate,
      stockNumber: newStockNumber,
      stockText: newStockText,
    };
    $.ajax({
      type: "POST",
      dataType: "json",
      url: "system/service/query.php",
      data: dataSet,
      success: function (response) {
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 5000,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
          },
          // didDestroy: (toast) => { //Si se cierra la notificación por tiempo: vacía los campos ingresados y cierra la ventana modal (exceptuando la fecha)
          //     $('#addStockNumber').val(0);
          //     $('#addStockText').val('');
          //     const modalInstance = bootstrap.Modal.getInstance($('#addStockModal')[0]);
          //     modalInstance.hide();
          // }
        });
        Toast.fire({
          icon: "success",
          title: response.message,
        });
      },
    });
    $("#substrStockNumber").val("");
    $("#substrStockText").val("");
    return;
  }
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
    },
  });
  Toast.fire({
    icon: "error",
    title: "Complete todos los campos!",
  });
});
function loadAllProducts() {
  $.ajax({
    type: "POST",
    url: "system/service/query.php",
    data: { action: "list" },
    dataType: "json",
    success: function (response) {
      $("#listProducts").empty();
      $.each(response.data, function (index, content) {
        buttonPlus = $("<button>").attr({
          id: "add_" + content.id,
          class: "btn btn-sm btn-success me-1",
          onclick: "modalAdd(this, " + content.id + ")",
        });
        buttonPlus.html('<i class="bi bi-plus-lg"></i>');
        buttonMinus = $("<button>").attr({
          id: "substract_" + content.id,
          class: "btn btn-sm btn-danger",
          onclick: "modalSubstract(this, " + content.id + ")",
        });
        buttonMinus.html('<i class="bi bi-dash-lg"></i>');
        tr = $("<tr>");
        tr.append(
          $("<td>")
            .text(content.codigo + " " + content.nombre)
            .attr({
              onclick: "loadStockData(" + content.id + ")",
              id: content.id,
            })
        );
        tr.append(
          $("<td>")
            .text(content.stock)
            .attr("onclick", "loadStockData(" + content.id + ")")
        );
        tr.append(
          $("<td>")
            .text(content.stock_minimo)
            .attr("onclick", "loadStockData(" + content.id + ")")
        );
        tr.append(
          $("<td>")
            .text(
              content.discontinuado == null ? "Activo" : content.discontinuado
            )
            .attr("onclick", "loadStockData(" + content.id + ")")
        );
        tr.append($("<td>").append(buttonPlus, buttonMinus));
        $("#listProducts").append(tr);
      });
    },
    error: function (xhr, status, error) {
      console.warn("Respuesta: " + status + "\n" + error);
    },
  });
}
function GetTodayDate() {
  var tdate = new Date();
  var DD = tdate.getDate(); //yields day
  var MM = tdate.getMonth(); //yields month
  var YYYY = tdate.getFullYear(); //yields year
  var currentDate = YYYY + "-" + (MM + 1) + "-" + DD;

  return currentDate;
}
function modalAdd(el, tableId) {
  showModal("addStockModal");
  $("#prodLabel").text($("td#" + tableId).text());
  $("#prodLabel").attr("data-prod", tableId);
  $("#addStockDate").val(GetTodayDate());
}
function modalSubstract(el, tableId) {
  showModal("substractStockModal");
  $("#substrProdLabel").text($("td#" + tableId).text());
  $("#substrProdLabel").attr("data-prod", tableId);
  $("#substrStockDate").val(GetTodayDate());
}
function actionRewindStock(el, tableId) {
  Swal.fire({
    title: "Estás seguro?",
    text: "No se puede revertir esta acción",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    cancelButtonText: "Cancelar",
    confirmButtonText: "Si, borrar movimiento",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        type: "POST",
        url: "system/service/query.php",
        data: {
          action: "rewind",
          stockId: tableId,
        },
        dataType: "json",
        success: function (response) {
          Swal.fire({
            title: "Borrado",
            text: response.message,
            icon: "success",
            didDestroy: () => {
              // loadStockData(response.data);
              $("#" + tableId).fadeOut("slow");
            },
          });
        },
      });
    }
  });
}
function loadStockData(tableId) {
  showModal("showStockModal");
  $.ajax({
    type: "POST",
    url: "system/service/query.php",
    data: { action: "listStock", prodId: tableId },
    dataType: "json",
    success: function (response) {
      $("#listStock").empty();
      $.each(response.data, function (index, content) {
        buttonRewind = $("<button>").attr({
          id: "rewind_" + content.id,
          class: "btn btn-sm btn-danger ",
          onclick: "actionRewindStock(this, " + content.id + ")",
        });
        buttonRewind.html('<i class="bi bi-x-lg"></i>');
        tr = $("<tr>");
        $(tr).attr('id',content.id);
        $("#productNameStock").html(
          $("<label>").text(
            "Producto: " + content.codigo + " " + content.nombre
          )
        );
        tr.append($("<td>").text(content.id_movimiento));
        tr.append($("<td>").text(content.fecha));
        tr.append($("<td>").text(content.tipo == "A" ? "Alta" : "Baja"));
        tr.append($("<td>").text(content.cantidad));
        tr.append($("<td>").text(content.observaciones));
        tr.append(buttonRewind);
        $("#listStock").append(tr);
      });
    },
    error: function (xhr, status, error) {
      console.warn("Respuesta: " + status + "\n" + error);
    },
  });
}
function showModal(modalId) {
  const modalInstance = bootstrap.Modal.getOrCreateInstance(
    $("#" + modalId)[0]
  );
  modalInstance.show();
}
