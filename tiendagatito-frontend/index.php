<?php
require 'resources/db.php';
require 'resources/dynamicsections.php';
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<?php
printHead();
?>

<body class="bg-pattern1">
  <?php
  printNavbar("Home", $link);
  ?>
  <div class="container-fluid px-0 pb-3">
    <div class="home-image">
      <div class="description text-center w-100 p-3">
        <img src="/assets/imgs/logo.png" alt="Tienda Gatito logo" height="200px">
        <h1>Tienda Gatito</h1>
        <h4>Tu tienda personal</h4>
      </div>
    </div>
    <div class="container sections py-3 mt-3">
      <div class="badge bg-blue rounded-pill px-3 py-2 mb-2 d-inline-flex align-items-center">
        <span class="material-icons ">grid_view</span>
        <span class="badge-text">Secciones</span>
      </div>
      <div class="row g-3 mb-2">
        <?php
        $query = "select * from sections";
        $result = mysqli_query($link, $query);
        $colw = 12;
        if (mysqli_num_rows($result) > 1) {
          $colw = 6;
        }
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
          <div class="col-<?= $colw ?> col-xxl-3 col-xl-4 col-lg-4 col-md-6 col-sm-6">
            <div class="section shadow-light" onclick="window.location.href = '/catalog/section/<?= $row['id'] ?>'" style="--section-bg-url: url('data:<?= getimagesizefromstring($row['image'])['mime'] ?>;base64,<?= base64_encode($row['image']) ?>');">
              <p><?= $row['name'] ?></p>
            </div>
          </div>
        <?php
        }
        ?>
      </div>
    </div>
    <div class="container products py-3">
      <?php
      $query = "SELECT pr.*,COUNT(pu.product) nPur FROM products pr JOIN purchases pu ON pr.id = pu.product GROUP BY pu.product ORDER BY nPur DESC LIMIT 12";
      $result = mysqli_query($link, $query);
      if ($result && mysqli_num_rows($result) > 0) {
      ?>
        <div class="badge bg-blue rounded-pill px-3 py-2 mb-2 d-inline-flex align-items-center">
          <span class="material-icons">trending_up</span>
          <span class="badge-text">Mas Vendido</span>
        </div>
        <div class="row g-3 card-group mb-4">
          <?php

          while ($row = mysqli_fetch_assoc($result)) {
          ?>
            <div class="col-12 col-xxl-2 col-xl-3 col-lg-3 col-md-4 col-sm-6">
              <div class="card product border-0 shadow h-100">
                <?php
                if ($row['stock'] <= 10 && $row['stock'] > 0) {    // danger - red
                ?>
                  <span class="badge stock rounded-pill bg-danger">
                    <?= $row['stock'] ?> en stock!
                  </span>
                <?php
                } else if ($row['stock'] == 0) {
                ?>
                  <span class="badge stock rounded-pill bg-secondary">
                    Fuera de Stock
                  </span>
                <?php
                }
                ?>
                <div class="bg-image">
                  <img src="data:<?= getimagesizefromstring($row['image'])['mime'] ?>;base64, <?= base64_encode($row['image']) ?>" loading="lazy" class="card-img-top" alt="...">
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title"><?= $row['name'] ?></h5>
                  <p class="card-text"><?= $row['description'] ?></p>
                  <div class="row align-items-center mt-auto">
                    <div class="col-8">
                      <p class="card-text cost"><?= $row['price'] ?> <a class="pts-logo absolute" title="Pts"></a></p>
                    </div>
                    <div class="col-4 d-flex justify-content-end">
                      <a role="button" <?= ($row['stock'] == 0 ? "disabled" : "") ?> class="btn btn-<?= ($row['stock'] == 0 ? "secondary" : "primary") ?> shadow-none buyButton" data-prodid="<?= $row['id'] ?>">Comprar</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php
          }
          ?>
        </div>
      <?php
      }

      $query = "SELECT *FROM products ORDER BY ID DESC LIMIT 6";
      $result = mysqli_query($link, $query);
      if ($result && mysqli_num_rows($result) > 0) {
      ?>
        <div class="badge bg-blue rounded-pill px-3 py-2 mb-2 d-inline-flex align-items-center">
          <span class="material-icons">new_releases</span>
          <span class="badge-text">Nuevos Productos</span>
        </div>
        <div class="row g-3 card-group mb-4">
          <?php

          while ($row = mysqli_fetch_assoc($result)) {
          ?>
            <div class="col-12 col-xxl-2 col-xl-3 col-lg-3 col-md-4 col-sm-6">
              <div class="card product border-0 shadow h-100">
                <?php
                if ($row['stock'] <= 10 && $row['stock'] > 0) {    // danger - red
                ?>
                  <span class="badge stock rounded-pill bg-danger">
                    <?= $row['stock'] ?> en stock!
                  </span>
                <?php
                } else if ($row['stock'] == 0) {
                ?>
                  <span class="badge stock rounded-pill bg-secondary">
                  Fuera de Stock
                  </span>
                <?php
                }
                ?>
                <div class="bg-image">
                  <img src="data:<?= getimagesizefromstring($row['image'])['mime'] ?>;base64, <?= base64_encode($row['image']) ?>" loading="lazy" class="card-img-top" alt="...">
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title"><?= $row['name'] ?></h5>
                  <p class="card-text"><?= $row['description'] ?></p>
                  <div class="row align-items-center mt-auto">
                    <div class="col-8">
                      <p class="card-text cost"><?= $row['price'] ?> <a class="pts-logo absolute" title="Pts"></a></p>
                    </div>
                    <div class="col-4 d-flex justify-content-end">
                      <a role="button" <?= ($row['stock'] == 0 ? "disabled" : "") ?> class="btn btn-<?= ($row['stock'] == 0 ? "secondary" : "primary") ?> shadow-none buyButton" data-prodid="<?= $row['id'] ?>">Comprar</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php
          }
          ?>
        </div>
      <?php
      }
      ?>
    </div>
  </div>

  <?php
  printFooter();
  ?>
  <script>
    $(function() {
      checkMarquee()
    })

    $(window).resize(function() {
      checkMarquee()
    })

    function checkMarquee() {
      $(".section p").each(function(i) {
        if (!$(this).hasClass("marquee")) {
          $txt = $(this).text()
        } else {
          $txt = $(this).find("span").first().text()
          $(this).html($txt)
          $(this).removeClass("marquee")
        }
        if (isEllipsisActive($(this))) {
          if (!$(this).hasClass("marquee")) {
            $(this).html("<span>" + $txt + "</span>" + "<span>" + $txt + "</span>")
            $(this).addClass("marquee")
          }
        }
      })
    }

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
          }
        })
      }
    })
  </script>
</body>

</html>
