<?php
require 'resources/db.php';
require 'resources/dynamicsections.php';

if (isset($_GET['id'])) {
  $id = mysqli_real_escape_string($link, $_GET['id']);
  $query = "select p.*, s.name sectName from products p join sections s on p.section = s.id where p.id = $id";
  $result = mysqli_query($link, $query);
  if (!$result) {
    $data = null;
    $toast['title'] = "Product info";
    $toast['description'] = "Error retrieving product data";
    $toast['type'] = "error";
    $_SESSION['toast'][] = $toast;
    header("location:/catalog");
    die();
  } else if (mysqli_num_rows($result) == 0) {
    $data = null;
    $toast['title'] = "Product info";
    $toast['description'] = "Product not found";
    $toast['type'] = "error";
    $_SESSION['toast'][] = $toast;
    header("location:/catalog");
    die();
  } else {
    $data = mysqli_fetch_assoc($result);
    $data['image'] = 'data:' . getimagesizefromstring($response['data']['image'])['mime'] . ';base64,' . base64_encode($data['image']);
  }
?>
  <!DOCTYPE html>
  <html lang="en">
  <?php
  printHead($data['name'] . " | Tienda Gatito");
  ?>

  <body class="bg-pattern1">
    <?php
    printNavbar("Product", $link);
    ?>
    <div class="container-fluid productPage">
      <div class="row px-2 mt-4" data-masonry='{"percentPosition": true }'>

        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-8 col-xxl-8 mb-4">
          <div class="card">
            <h5 class="card-header py-2">Detalles del producto</h5>
            <div class="card-body">
              <div class="row">
                <div class="col-10 col-sm-10 mx-auto col-md-5 col-lg-5 col-xl-5 col-xxl-5 d-flex justify-content-center align-items-center px-3 mb-2">
                  <img src="<?= $data['image'] ?>" alt="product photo" class="ms-2 my-2 p-0 w-100 rounded-2" onerror="this.src = 'assets/imgs/prodImagePreview.jpg';">
                </div>
                <div class="col-12 col-sm-12 col-md-7 col-lg-7 col-xl-7 col-xxl-7 d-flex flex-column px-3 justify-content-center">
                  <div class="form-floating border-0 mb-3 shadow-sm">
                    <input type="text" id="prodName" value="<?= $data['name'] ?>" disabled class="form-control" placeholder="Not available" aria-label="Product name" aria-describedby="Product name">
                    <label for="prodName">Nombre del producto</label>
                  </div>

                  <div class="form-floating border-0 mb-3 shadow-sm">
                    <textarea class="form-control" placeholder="Not available" id="Description" disabled><?= $data['description'] ?></textarea>
                    <label for="Description">Descripcion</label>
                  </div>

                  <div class="form-floating border-0 mb-3 shadow-sm">
                    <p id="prodPrice" disabled class="form-control mb-0" aria-label="Points" aria-describedby="Points"><?= $data['price'] ?><a class="ms-1 pts-logo absolute" title="Pts"></a></p>
                    <label for="prodPrice">Precio</label>
                  </div>

                  <h4><a href="/catalog/section/<?= $data['section'] ?>"><span class="badge bg-secondary" role="button">#<?= $data['sectName'] ?></span></a></h4>

                </div>
              </div>

            </div>
            <div class="card-footer d-flex">
              <?php
                if($data['stock'] == 0){
                  ?>
                  <button type="button" disabled class="btn btn-secondary btn-lg px-4 my-1 mx-auto"><span class="material-icons buttonIcon">close</span> Fuera de stock</button>
                  <?php
                }else{
                  ?>
                  <button type="button" id="buyBtn" class="btn btn-primary btn-lg px-4 my-1 mx-auto"><span class="material-icons buttonIcon">shopping_cart</span> Comprar</button>
                  <?php
                }
              ?>
              
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-4 col-xxl-4 mb-4">
          <div class="card">
            <h5 class="card-header py-2">Estado</h5>
            <div class="card-body">
              <div class="row">
                <div class="col-12 d-flex flex-column px-3 align-items-center">
                  <div class="form-floating w-100 border-0 mb-3 shadow-sm">
                    <input type="text" id="inStock" value="<?= $data['stock'] ?>" disabled class="form-control" placeholder="Not available" aria-label="Product name" aria-describedby="Product name">
                    <label for="inStock">Candidad en stock</label>
                  </div>

                  <div class="form-floating w-100 border-0 mb-3 shadow-sm">
                    <?php
                    $soldquery = "select count(*) sold from purchases where product = '$id'";
                    $soldres = mysqli_query($link, $soldquery);
                    $soldData = array();
                    if (!$soldres) {
                      $soldData = array("sold" => "0");
                    } else {
                      $soldData = mysqli_fetch_assoc($soldres);
                    }
                    ?>
                    <input type="text" id="itemsSold" value="<?= $soldData['sold'] ?>" disabled class="form-control" placeholder="Not available" aria-label="Product name" aria-describedby="Product name">
                    <label for="itemsSold">Candidad comprada</label>
                  </div>

                  <div class="row stats w-100 align-items-start">
                    <?php
                    if ($data['stock'] == 0) {
                    ?>
                      <div class="col-md-auto me-2 px-0">
                        <div class="badge bg-brown rounded-pill px-3 py-2 mb-2 d-inline-flex align-items-center">
                          <span class="material-icons">close</span>
                          <span class="badge-text">Fuera de stock</span>
                        </div>
                      </div>
                    <?php
                    } else if ($data['stock'] > 0 && $data['stock'] <= 10) {
                    ?>
                      <div class="col-md-auto me-2 px-0">
                        <div class="badge bg-danger rounded-pill px-3 py-2 mb-2 d-inline-flex align-items-center">
                          <span class="material-icons">warning</span>
                          <span class="badge-text">Últimas piezas</span>
                        </div>
                      </div>
                    <?php
                    }

                    $bsquery = "SELECT product, COUNT(product) nPur FROM purchases GROUP BY product ORDER BY nPur DESC LIMIT 12";
                    $bsres = mysqli_query($link, $bsquery);
                    $isBestSeller = false;
                    if ($bsres) {
                      while ($row = mysqli_fetch_assoc($bsres)) {
                        if ($row['product'] == $id) {
                          $isBestSeller = true;
                          break;
                        }
                      }
                    }
                    if ($isBestSeller) {
                    ?>
                      <div class="col-md-auto me-2 px-0">
                        <div class="badge bg-blue rounded-pill px-3 py-2 mb-2 d-inline-flex align-items-center">
                          <span class="material-icons">trending_up</span>
                          <span class="badge-text">Mas Vendido</span>
                        </div>
                      </div>
                    <?php
                    }

                    $newquery = "SELECT id FROM products ORDER BY ID DESC LIMIT 6";
                    $newres = mysqli_query($link, $newquery);
                    $isNew = false;
                    if ($newres) {
                      while ($row = mysqli_fetch_assoc($newres)) {
                        if ($row['id'] == $id) {
                          $isNew = true;
                          break;
                        }
                      }
                    }
                    if ($isNew) {
                    ?>
                      <div class="col-md-auto me-2 px-0">
                        <div class="badge bg-blue rounded-pill px-3 py-2 mb-2 d-inline-flex align-items-center">
                          <span class="material-icons">new_releases</span>
                          <span class="badge-text">Nuevo producto</span>
                        </div>
                      </div>
                    <?php
                    }
                    ?>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
    <div class="modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="purchaseModal" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-w-auto">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="purchaseModalTitle">Confirmar la compra</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="fs-6" id="confirmPurModalBody">¿Quiere comprar este producto?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-blue" id="purchaseModalConfirm">Confirmar</button>
          </div>
        </div>
      </div>
    </div>
    <?php
    printFooter();
    ?>
    <script>
      $("#buyBtn").click(function(e) {
        $.ajax({
          method: 'GET',
          url: "/api/makePurchase",
          data: {
            product: <?= $id ?>,
            page: "local"
          },
          dataType: 'json',
          success: function(response) {
            if (response.statusCode == 401) {
              window.location.replace("/twitchlogin")
            } else if (response.statusCode == 200) {
              purchaseModal.show()
            }else{
              showToast(response.toast)
            }
          }
        })
      })

      $("#purchaseModalConfirm").click(function(e) {
        $.ajax({
          method: 'POST',
          url: "/api/makePurchase",
          data: {
            product: <?= $id ?>
          },
          dataType: 'json',
          success: function(response) {
            if (response.statusCode == 401) {
              window.location.replace("/twitchlogin")
            }else if (response.statusCode == 200) {
              $(".nav-pts .amount").text(response.remainingPts)
              $("#inStock").val(parseInt($("#inStock").val())-1)
              $("#itemsSold").val(parseInt($("#itemsSold").val())+1)
            }else if(response.statusCode == 403 && response.message == "Missing profile data"){
              showToast({title:"Missing profile data", description:"Go to your <a href='/account' target='_blank' class='text-white'>account page</a> and fill up the missing fields, then try again", type:"warning", delay:10000})
            }

            if (response.toast) {
              showToast(response.toast)
            }
            purchaseModal.hide()
          }
        })
      })

      purchaseModal = null
      $(function() {
        purchaseModal = new bootstrap.Modal(document.getElementById('purchaseModal'), {})
        <?php
        if (isset($_SESSION['confirmPurchase']) && $_SESSION['confirmPurchase']) {
        ?>
          purchaseModal.show()
        <?php
          unset($_SESSION['confirmPurchase']);
        }
        ?>
        textAreaResize()
      })

      $(window).resize(function() {
        textAreaResize()
      })

      function textAreaResize() {
        $("textarea").css({
          "height": "1px"
        })
        $("textarea").css({
          "height": (2 + $("textarea").prop('scrollHeight')) + "px"
        })
      }
    </script>
  </body>

  </html>
<?php
} else {
  http_response_code(404);
}
?>