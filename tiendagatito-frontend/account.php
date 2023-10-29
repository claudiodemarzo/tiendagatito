<!DOCTYPE html>
<?php
require 'resources/db.php';
require 'resources/dynamicsections.php';
?>
<html>
<?php printHead("Account | Tienda Gatito"); ?>

<body class="bg-pattern1">
  <?php
  checkLogin("/account");
  printNavbar("Account", $link);
  if (isset($_SESSION['loginStatus']) && $_SESSION['loginStatus']) {
    $userdata = $_SESSION['data'];
    $getq = mysqli_query($link, "SELECT * FROM users WHERE twitchID='$userdata->id'");
    if ($getq) {
      $accDetails = mysqli_fetch_assoc($getq);
    } else {
      $toast['title'] = "Error";
      $toast['type'] = "error";
      $toast['description'] = "Error loading information";
      $_SESSION['toast'][] = $toast;
    }

    $getp = mysqli_query($link, "SELECT pu.id id, pr.name, pu.purchase_datetime, pu.points FROM purchases pu JOIN products pr ON pu.product=pr.id  WHERE user='$userdata->id' ORDER BY pu.purchase_datetime DESC");

    if ($getp) {
      $orders = array();
      while ($row = mysqli_fetch_assoc($getp)) {
        $orders[] = $row;
      }
    } else {
      $toast['title'] = "Error";
      $toast['type'] = "error";
      $toast['description'] = "Error loading your orders";
      $_SESSION['toast'][] = $toast;
    }
  }
  ?>
  <div class="row mx-3 mt-4" data-masonry='{"percentPosition": true }'>
    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-6 col-xxl-6 mb-4">
      <div class="card">
        <h5 class="card-header">Detalles de cuenta</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 d-flex justify-content-center align-items-center">
              <img src="<?= $userdata->profile_image_url ?>" alt="profile picture" class="prof-pic-big mx-auto my-2 p-0" onerror="if (this.src != 'error.jpg') this.src = 'assets/imgs/placeholder-profpic.png';">
            </div>
            <div class="col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 pt-3">
              <div class="input-group mb-3 w-75 mx-auto">
                <span class="input-group-text" id="Username">@</span>
                <input type="text" value="<?= $userdata->display_name ?>" disabled class="form-control" placeholder="Not available" aria-label="Username" aria-describedby="Username">
              </div>

              <div class="input-group mb-3 w-75 mx-auto">
                <span class="input-group-text" id="Description">Biografia</span>
                <input type="text" value="<?= $userdata->description ?>" disabled class="form-control" placeholder="Not available" aria-label="Description" aria-describedby="Description">
              </div>

              <div class="input-group mb-3 w-75 mx-auto">
                <span class="input-group-text" id="PointsIco"><i class="pts-logo"></i></span>
                <input type="text" value="<?= $accDetails['points'] ?>" disabled class="form-control" placeholder="Not available" aria-label="Points" aria-describedby="Points">
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-6 col-xxl-6 mb-4">
      <div class="card">
        <h5 class="card-header">Informacion de envio</h5>
        <div class="card-body">
          <form id="shipinfoform">
            <div class="input-group mb-3 w-100">
              <span class="input-group-text" id="FullName">Nombre Completo</span>
              <input type="text" name="fullName" value="<?= $accDetails['fullName'] ?>" class="form-control" placeholder="Your full name" aria-label="FullName" aria-describedby="FullName">
            </div>

            <div class="input-group mb-3 w-100">
              <span class="input-group-text" id="Email">Correo</span>
              <input type="email" name="email" value="<?= $accDetails['email'] ?>" class="form-control" placeholder="Email" aria-label="Email" aria-describedby="Email">
            </div>

            <div class="input-group mb-3 w-100">
              <span class="input-group-text" id="PhoneNumber">Numero de telefono</span>
              <input type="tel" name="phoneNumber" value="<?= $accDetails['phoneNumber'] ?>" class="form-control" placeholder="Phone number" aria-label="PhoneNumber" aria-describedby="PhoneNumber">
            </div>

            <div class="input-group mb-3 w-100">
              <span class="input-group-text" id="AddrLine1">Direccion linea 1</span>
              <input type="text" name="addressOne" value="<?= $accDetails['addressOne'] ?>" class="form-control" placeholder="Address and house number #1" aria-label="AddrLine1" aria-describedby="AddrLine1">
            </div>

            <div class="input-group mb-3 w-100">
              <span class="input-group-text" id="AddrLine2">Direccion linea 2</span>
              <input type="text" name="addressTwo" value="<?= $accDetails['addressTwo'] ?>" class="form-control" placeholder="Address and house number #2" aria-label="AddrLine2" aria-describedby="AddrLine2">
            </div>

            <div class="input-group mb-3 w-100">
              <span class="input-group-text" id="City">Cuidad</span>
              <input type="text" name="city" value="<?= $accDetails['city'] ?>" class="form-control" placeholder="City" aria-label="City" aria-describedby="City">
            </div>

            <div class="input-group mb-3 w-100">
              <span class="input-group-text" id="State">Estado</span>
              <input type="text" name="state" value="<?= $accDetails['state'] ?>" class="form-control" placeholder="State" aria-label="State" aria-describedby="State">
            </div>

            <div class="input-group mb-3 w-100">
              <span class="input-group-text" id="PostalCode">Codigo postal</span>
              <input type="text" name="postalCode" value="<?= $accDetails['postalCode'] ?>" class="form-control" placeholder="Postal code" aria-label="PostalCode" aria-describedby="PostalCode">
            </div>

            <div class="input-group mb-3 w-100">
              <span class="input-group-text" id="Country">Pais</span>
              <input type="text" name="country" value="<?= $accDetails['country'] ?>" class="form-control" placeholder="Country" aria-label="Country" aria-describedby="Country">
            </div>

          </form>

          <button type="button" class="btn btn-primary" id="saveShipInfo">Guardar</button>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-6 col-xxl-6 mb-4">
      <div class="card">
        <h5 class="card-header">Tus pedidos</h5>
        <div class="card-body overflow-auto">
          <table class="table orders mb-0">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Producto</th>
                <th scope="col">Fecha</th>
                <th scope="col">Puntos</th>
                <th scope="col">Informacion</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if (count($orders) > 0) {
                foreach ($orders as $order) {
              ?>
                  <tr>
                    <th scope="row"><?= $order['id'] ?></th>
                    <td><?= $order['name'] ?></td>
                    <td><?= $order['purchase_datetime'] ?></td>
                    <td><?= $order['points'] ?></td>
                    <td><button class='btn table-moreinfoorder-btn btn-primary' orderID='<?= $order['id'] ?>'>Informacion</button></td>
                  </tr>
                <?php
                }
              } else {
                ?>
                <tr>
                  <td class="text-center" colspan="5">No has hecho ninguna compra aun</td>
                </tr>
              <?php
              } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="orderInfoModal" tabindex="-1" aria-labelledby="orderInfoModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-w-auto">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Informacion de pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="accordion" id="accordionPanelsStayOpen">
            <div class="accordion-item">
              <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                <button class="accordion-button collapsed focus-none" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="false" aria-controls="panelsStayOpen-collapseOne">
                  Detalles
                </button>
              </h2>
              <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingOne">
                <div class="accordion-body">
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Pedido #</span>
                    <input type="text" disabled id="Modal_OrderNum" class="form-control" placeholder="" aria-label="OrderNum" aria-describedby="OrderNum">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Fecha</span>
                    <input type="text" disabled id="Modal_Timestamp" class="form-control" placeholder="" aria-label="Timestamp" aria-describedby="Timestamp">
                  </div>
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
                <button class="accordion-button focus-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                  Informacion del Producto
                </button>
              </h2>
              <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingTwo">
                <div class="accordion-body">
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Nombre del producto</span>
                    <input type="text" disabled id="Modal_ProductName" class="form-control" placeholder="" aria-label="ProductName" aria-describedby="ProductName">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Descripcion</span>
                    <input type="text" disabled id="Modal_Description" class="form-control" placeholder="" aria-label="Description" aria-describedby="Description">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Precio</span>
                    <input type="number" disabled id="Modal_Price" class="form-control" placeholder="" aria-label="Price" aria-describedby="Price">
                  </div>
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                <button class="accordion-button collapsed focus-none" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                  Informacion de envio y contacto
                </button>
              </h2>
              <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingThree">
                <div class="accordion-body">
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Nombre Completo</span>
                    <input type="text" disabled id="Modal_FullName" class="form-control" placeholder="Full name" aria-label="FullName" aria-describedby="FullName">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Correo</span>
                    <input type="email" disabled id="Modal_Email" class="form-control" placeholder="Email" aria-label="Email" aria-describedby="Email">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Numero de telefono</span>
                    <input type="tel" disabled id="Modal_PhoneNumber" class="form-control" placeholder="Phone number" aria-label="PhoneNumber" aria-describedby="PhoneNumber">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Direccion linea 1</span>
                    <input type="text" disabled id="Modal_AddrLine1" class="form-control" placeholder="Address and house number #1" aria-label="AddrLine1" aria-describedby="AddrLine1">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Direccion linea 2</span>
                    <input type="text" disabled id="Modal_AddrLine2" class="form-control" placeholder="Address and house number #2" aria-label="AddrLine2" aria-describedby="AddrLine2">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Cuidad</span>
                    <input type="text" disabled id="Modal_City" class="form-control" placeholder="City" aria-label="City" aria-describedby="City">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Estado</span>
                    <input type="text" disabled id="Modal_State" class="form-control" placeholder="State" aria-label="State" aria-describedby="State">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Codigo postal</span>
                    <input type="text" disabled id="Modal_PostalCode" class="form-control" placeholder="Postal code" aria-label="PostalCode" aria-describedby="PostalCode">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Pais</span>
                    <input type="text" disabled id="Modal_Country" class="form-control" placeholder="Country" aria-label="Country" aria-describedby="Country">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" async></script>
  <?php printFooter(); ?>
  <script>
    var orderInfoModal = null

    $(function() {
      orderInfoModal = new bootstrap.Modal(document.getElementById('orderInfoModal'))
    })

    $("#saveShipInfo").click(function() {
      fd = new FormData(document.getElementById("shipinfoform"))
      $.ajax({
        method: 'POST',
        dataType: 'json',
        url: 'https://tiendagatito.com/api/editUser',
        data: fd,
        processData: false,
        contentType: false,
        cache: false,
        success: function(response) {
          showToast(response.toast)
        }
      })
    })

    $(".table-moreinfoorder-btn").click(function(evt) {
      evt.preventDefault()
      const orderID = $(this).attr('orderID')
      $.get('https://tiendagatito.com/api/purchases?id=' + orderID, function(data) {
        var od = data.data
        $("#Modal_ProductName").val(od.prodName)
        $("#Modal_Description").val(od.prodDesc)
        $("#Modal_Price").val(od.prodPrice)
        $("#Modal_FullName").val(od.fullName)
        $("#Modal_Email").val(od.email)
        $("#Modal_PhoneNumber").val(od.phoneNumber)
        $("#Modal_AddrLine1").val(od.addressOne)
        $("#Modal_AddrLine2").val(od.addressTwo)
        $("#Modal_City").val(od.city)
        $("#Modal_State").val(od.state)
        $("#Modal_PostalCode").val(od.postalCode)
        $("#Modal_Country").val(od.country)
        $("#Modal_Timestamp").val(od.purchase_datetime)
        $("#Modal_OrderNum").val(od.id)
        orderInfoModal.show()
      })
    })
  </script>
</body>

</html>