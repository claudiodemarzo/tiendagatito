<?php
require 'resources/db.php';
require 'resources/dynamicsections.php';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<?php
printHead("Catalog | Tienda Gatito");
?>

<body class="bg-pattern1">
  <?php printNavbar("Catalog", $link); ?>
  <div class="container-fluid catalog">
    <div class="row">
      <div class="col-12 col-xxl-2 col-xl-3 col-lg-3 col-md-4 col-sm-12 py-3 px-3" id="filtersCol">
        <h3 class="text-white">Filtros</h3>
        <div class="filters" id="filtersDiv">
          <div class="input-group search-group mb-3">
            <span class="input-group-text material-icons">search</span>
            <input type="text" id="searchProd" class="form-control focus-none" placeholder="Busca un producto" aria-label="Search">
          </div>
          <ul class="list-group mb-3">
            <?php
            $sections = mysqli_query($link, "SELECT COUNT(p.id) nProd,s.* FROM sections s LEFT JOIN products p ON s.id=p.section GROUP BY s.id");
            if (!$sections) {
              $toast['title'] = "Error";
              $toast['type'] = "error";
              $toast['description'] = "Error loading sections";
              $_SESSION['toast'][] = $toast;
            } else {
              while ($row = mysqli_fetch_array($sections)) {
            ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <input class="form-check-input me-1" type="checkbox" value="<?= $row['id'] ?>" aria-label="..." <?= (isset($_GET['section']) && $_GET['section'] == $row['id']) ? "checked" : "" ?>>
                  <?= $row['name'] ?>
                  <span class="badge bg-primary rounded-pill"><?= $row['nProd'] ?></span>
                </li>
            <?php
              }
            }
            ?>

          </ul>
          <div class="price-slider bg-light rounded-3 p-3">
            <label for="customRange2" class="form-label">Precio Maximo</label>
            <input type="range" class="form-range focus-none" min="0" max="5" id="customRange2" disabled>
            <div class="price-labels">
              <span class="minPrice"></span>
              <span class="maxPrice"></span>
              <span class="maxSelPrice"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xxl-10 col-xl-9 col-lg-9 col-md-8 col-sm-12 container products py-3">
        <h3 class="text-white">Productos</h3>
        <div class="row g-3" id="prodRow">
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="filtersPopup" data-bs-keyboard="false" tabindex="-1" aria-labelledby="filtersPopup" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">Filters</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body filters mb-3" id="filtersPopupBody">

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary">Delete filters</button>
        </div>
      </div>
    </div>
  </div>

  <?php printFooter(); ?>
  <script>
    function loadProducts(fromSlider = false) {
      $("#prodRow").html(`<div class="d-flex justify-content-center mt-5"><div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span></div></div>`)
      filters = []
      query = $("#searchProd").val()
      priceSlider = null
      if (fromSlider) {
        priceSlider = $(".form-range").val()
      }
      $(".filters .form-check-input").each(function(i) {
        if ($(this).is(":checked")) {
          filters.push($(this).val())
        }
      })
      filters = JSON.stringify(filters)
      $.ajax({
        method: 'GET',
        url: "/api/products",
        dataType: 'json',
        data: {
          sections: filters,
          search: query,
          maxPrice: priceSlider
        },
        success: function(response) {
          $("#prodRow").html("")
          minPrice = null
          maxPrice = null
          if (response.data.length > 0) {
            $.each(response.data, function(i, p) {
              p.price = parseInt(p.price)
              if (p.price < minPrice || !minPrice) {
                minPrice = p.price
              }
              if (p.price > maxPrice || !maxPrice) {
                maxPrice = p.price
              }

              $("#prodRow").append(`<div class="col-12 col-xxl-2 col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="card product border-0 shadow h-100">
                  ` + (p.stock <= 10 && p.stock > 0 ? `<span class="badge stock rounded-pill bg-danger">` + p.stock + ` en stock!</span>` : (p.stock == 0 ? `<span class="badge stock rounded-pill bg-secondary">Fuera de stock</span>` : ``)) + `
                  <div class="bg-image">
                    <img src="` + p.image + `" loading="lazy" class="card-img-top" alt="` + p.name + `">
                  </div>
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title">` + p.name + `</h5>
                    <p class="card-text">` + p.description + `</p>
                    <div class="row align-items-center mt-auto">
                      <div class="col-8">
                        <p class="card-text cost">` + p.price + `<a class="pts-logo absolute" title="Pts"></a></p>
                      </div>
                      <div class="col-4 d-flex justify-content-end">
                        <a role="button" ` + (p.stock == 0 ? `disabled ` : '') + `data-prodid="` + p.id + `" class="btn btn-` + (p.stock == 0 ? 'secondary' : 'primary') + ` shadow-none buyButton">Comprar</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>`)
            })
            $(".price-slider .form-range").prop("disabled", false).attr("min", minPrice)
            if (!fromSlider) {
              $(".price-slider .form-range").attr("max", maxPrice).val(maxPrice)
              $(".price-slider .minPrice").text(minPrice)
              $(".price-slider .maxPrice").text(maxPrice)
            }
          } else {
            $(".price-slider .form-range").prop("disabled", true)
            $(".price-slider .minPrice").text("min")
            $(".price-slider .maxPrice").text("max")
            $(".price-slider .maxSelPrice").text("")
            $("#prodRow").html(`<div class="d-flex justify-content-center mt-5"><div class="not-found-message">No se encontraron productos</div></div>`)
          }
        }
      })
    }

    var isMobileMode = false
    var win = $(window)
    var filtersPopup = null

    $(function() {
      window.history.replaceState({}, document.title, "/catalog")
      loadProducts()
      filtersPopup = new bootstrap.Modal(document.getElementById('filtersPopup'))
      responsiveCheck()
    })

    $(document).on("change", ".filters .form-check-input", function(e) {
      loadProducts()
    })

    $(document).on("keyup", "#searchProd", function(e) {
      loadProducts()
    })

    $(document).on("change", ".form-range", function(e) {
      loadProducts(true)
      $(".maxSelPrice").hide("slow")
    })

    $(document).on("input", ".form-range", function(e) {
      var xPos = 0,
        val = $(this).val(),
        minP = $(this).attr("min"),
        maxP = $(this).attr("max")
      xPos = (val - minP) / (maxP - minP) * 100
      handlerW = 15.5
      $(".maxSelPrice").text($(this).val()).show("fast").css({
        "left": "calc(" + xPos + " / 100 * (100% - " + handlerW + "px) + " + (handlerW / 2) + "px)"
      })
    })

    $(window).resize(function() {
      responsiveCheck()
    })

    $(document).on("click", ".card.product .bg-image,.card.product .card-title", function(e) {
      prodId = $(this).closest(".card.product").find(".buyButton").data("prodid")
      window.location.href = "/product/" + prodId
    })

    $(document).on("click", ".buyButton", function(e) {
      e.stopPropagation();
      if (!$(this).hasClass("btn-secondary")) {
        prodId = $(this).data("prodid")
        $.ajax({
          method: 'GET',
          url: "/api/makePurchase",
          data: {
            product: prodId
          },
          dataType: 'json',
          success: function(response) {
            if (response.statusCode == 401) {
              window.location.replace("/twitchlogin")
            } else if (response.statusCode == 200) {
              window.location.href = "/product/" + prodId
            }
            if(response.toast){
              showToast(response.toast)
            }
            
          }
        })
      }
    })

    function responsiveCheck() {
      var $sectHtml = $("")
      isMobileMode = win.width() < 767
      if (isMobileMode) {
        $sectHtml = $("#filtersDiv").children().clone(true, true)
        if (!isEmpty($sectHtml)) {
          $("#filtersPopupBody").html($sectHtml)
          $("#filtersDiv").html("")
          $("#filtersCol").hide()
        }
      } else {
        filtersPopup.hide()
        $sectHtml = $("#filtersPopupBody").children().clone(true, true)
        if (!isEmpty($sectHtml)) {
          $("#filtersDiv").html($sectHtml)
          $("#filtersPopupBody").html("")
          $("#filtersCol").show()
        }
      }
    }

    function isEmpty($el) {
      return !$.trim($el.html())
    }
  </script>

</body>