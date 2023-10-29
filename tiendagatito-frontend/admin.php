<?php
include('resources/db.php');
include('resources/dynamicsections.php');
$accessToken = $_SESSION['data']->accessToken;
$curl = curl_init("https://id.twitch.tv/oauth2/validate");

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$headers = array();
$headers[0] = 'Authorization: Bearer ' . $accessToken;
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$api_response = curl_exec($curl);
curl_close($curl);
$api_response = json_decode($api_response, true);
if (isset($api_response['status']) || !in_array($api_response['user_id'], $adminIDs)) {
  header("location:/404");
  die();
}
?>
<html lang="en" dir="ltr">
<?php
printHead("Admin Panel | Tienda Gatito");
?>

<body>
  <?php printNavbar("Admin Panel", $link); ?>
  <div class="container-fluid px-3 pb-3">
    <div class="d-flex align-items-start mt-3 col">
      <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
        <button class="nav-link active" id="purchases-list-tab" data-bs-toggle="pill" data-bs-target="#purchases-list" type="button" role="tab" aria-controls="purchases-list" aria-selected="true">Recent Purchases</button>
        <button class="nav-link" id="sections-tab" data-bs-toggle="pill" data-bs-target="#sections" type="button" role="tab" aria-controls="sections" aria-selected="false">Sections</button>
        <button class="nav-link" id="products-tab" data-bs-toggle="pill" data-bs-target="#products" type="button" role="tab" aria-controls="products" aria-selected="false">Products</button>
        <button class="nav-link" id="season-tab" data-bs-toggle="pill" data-bs-target="#season" type="button" role="tab" aria-controls="season" aria-selected="false">Season</button>
      </div>
      <div class="tab-content col admin" id="v-pills-tabContent">

        <div class="tab-pane fade show active" id="purchases-list" role="tabpanel">
          <div class="container">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Order #</th>
                  <th scope="col">Product Name</th>
                  <th scope="col">Buyer's Twitch ID</th>
                  <th scope="col">More Info</th>
                </tr>
              </thead>
              <tbody id="purchasesTbody">
                <?php
                $query = 'select pur.id, pro.name, usr.twitchID from purchases pur join products pro on pur.product = pro.id join users usr on pur.user = usr.twitchID order by purchase_datetime desc';
                $result = mysqli_query($link, $query);
                while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
                ?>
                  <tr>
                    <th scope="row"><?= $row[0] ?></th>
                    <td><?= $row[1] ?></td>
                    <td><?= $row[2] ?></td>
                    <td><button class='btn table-moreinfopur-btn btn-primary' purchaseID='<?= $row[0] ?>'>More info</button></td>
                  </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
            <div id='moreinfoSec'>
            </div>
          </div>
        </div>

        <div class="tab-pane fade" id="sections" role="tabpanel">
          <div class="container">
            <div class="d-flex"><button class='btn opener-createsec-btn btn-blue ms-auto'>New Section</button></div>

            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Section #</th>
                  <th scope="col">Name</th>
                  <th scope="col">Edit</th>
                  <th scope="col">Delete</th>
                </tr>
              </thead>
              <tbody id="sectionsTbody">
                <?php
                $query = 'select id, name from sections';
                $result = mysqli_query($link, $query);
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                ?>
                  <tr>
                    <th scope="row"><?= $row['id'] ?></th>
                    <td><?= $row['name'] ?></td>
                    <td><button class='btn table-editsec-btn btn-primary' sectionID='<?= $row['id'] ?>'>Edit</button></td>
                    <td><button class='btn table-deletesec-btn btn-danger' sectionID='<?= $row['id'] ?>'>Delete</button></td>
                  </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="tab-pane fade" id="products" role="tabpanel">
          <div class="container">
            <div class="d-flex"><button class='btn opener-addproduct-btn btn-blue ms-auto'>New Product</button></div>
            <table class="table">
              <thead>
                <th scope="col">Product #</th>
                <th scope="col">Name</th>
                <th scope="col">Price</th>
                <th scope="col">Edit</th>
                <th scope="col">Delete</th>
              </thead>
              <tbody id="productsTbody">
                <?php
                $query = 'select id, name, price from products';
                $result = mysqli_query($link, $query);
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                ?>
                  <tr>
                    <th scope="row"><?= $row['id'] ?></th>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['price'] ?></td>
                    <td><button class='btn table-editprod-btn btn-primary' productID='<?= $row['id'] ?>'>Edit</button></td>
                    <td><button class='btn table-deleteprod-btn btn-danger' productID='<?= $row['id'] ?>'>Delete</button></td>
                  </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="tab-pane fade" id="season" role="tabpanel">
          <div class="container d-flex justify-content-center">
            <?php
            $query = 'select * from season';
            $result = mysqli_query($link, $query);
            if ($result) {
              $seasonInfo = mysqli_fetch_array($result);
            ?>
              <div class="col-12 col-xxl-4 col-xl-5 col-lg-6 col-md-8 col-sm-10">
                <div class="col mb-5">
                  <button class="btn resetseason-btn btn-danger mb-1">Reset current season</button>
                  <small class="text-muted"><br>It set the users points to 0 and reset the start day of the season </small>
                </div>
                <div class="input-group mb-3 w-100">
                  <span class="input-group-text">Season number</span>
                  <input type="number" class="form-control" placeholder="" id="seasonNumber" min="0" value="<?= $seasonInfo['number'] ?>">
                </div>
                <div class="input-group mb-3 w-100">
                  <span class="input-group-text">Season name</span>
                  <input type="text" class="form-control" placeholder="" id="seasonName" value="<?= $seasonInfo['name'] ?>">
                </div>
                <div class="col mt-5 mb-3">
                  <div class="input-group  mb-1 w-100">
                    <span class="input-group-text">Season end</span>
                    <input type="datetime-local" class="form-control" placeholder="" id="seasonEnd" value="<?= date("Y-m-d\TH:i:s", strtotime($seasonInfo['end'])) ?>">
                  </div>
                  <small class="text-muted">Only for countdown</small>
                </div>
                <button class="btn setseasondetails-btn btn-blue">Update season info</button>
              </div>
            <?php
            }

            ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="orderInfoModal" tabindex="-1" aria-labelledby="orderInfoModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-w-auto">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="orderInfoModalLabel">Order info</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="accordion" id="accordionPanelsStayOpen">
            <div class="accordion-item">
              <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                <button class="accordion-button collapsed focus-none" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="false" aria-controls="panelsStayOpen-collapseOne">
                  Details
                </button>
              </h2>
              <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingOne">
                <div class="accordion-body">
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Order #</span>
                    <input type="text" disabled id="Modal_OrderNum" class="form-control" placeholder="" aria-label="OrderNum" aria-describedby="OrderNum">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Date</span>
                    <input type="text" disabled id="Modal_Date" class="form-control" placeholder="" aria-label="Date" aria-describedby="Date">
                  </div>
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
                <button class="accordion-button collapsed focus-none" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                  User info
                </button>
              </h2>
              <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingTwo">
                <div class="accordion-body">
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Twitch ID</span>
                    <input type="text" disabled id="Modal_TwitchID" class="form-control" placeholder="" aria-label="TwitchID" aria-describedby="TwitchID">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Twitch display name</span>
                    <input type="text" disabled id="Modal_DisplayName" class="form-control" placeholder="Twitch display name" aria-label="FullName" aria-describedby="DisplayName">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Email</span>
                    <input type="email" disabled id="Modal_Email" class="form-control" placeholder="Email" aria-label="Email" aria-describedby="Email">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Phone number</span>
                    <input type="tel" disabled id="Modal_PhoneNumber" class="form-control" placeholder="Phone number" aria-label="PhoneNumber" aria-describedby="PhoneNumber">
                  </div>
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                <button class="accordion-button collapsed focus-none" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                  Product info
                </button>
              </h2>
              <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingThree">
                <div class="accordion-body">
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Product ID</span>
                    <input type="text" disabled id="Modal_ProductID" class="form-control" placeholder="" aria-label="ProductID" aria-describedby="ProductID">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Product name</span>
                    <input type="text" disabled id="Modal_ProductName" class="form-control" placeholder="" aria-label="ProductName" aria-describedby="ProductName">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Description</span>
                    <input type="text" disabled id="Modal_Description" class="form-control" placeholder="" aria-label="Description" aria-describedby="Description">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Points spent</span>
                    <input type="number" disabled id="Modal_Price" class="form-control" placeholder="" aria-label="Price" aria-describedby="Price">
                  </div>
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="panelsStayOpen-headingFour">
                <button class="accordion-button collapsed focus-none" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseFour" aria-expanded="false" aria-controls="panelsStayOpen-collapseFour">
                  Shipment info
                </button>
              </h2>
              <div id="panelsStayOpen-collapseFour" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingFour">
                <div class="accordion-body">
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Full name</span>
                    <input type="text" disabled id="Modal_FullName" class="form-control" placeholder="Full name" aria-label="FullName" aria-describedby="FullName">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Address line 1</span>
                    <input type="text" disabled id="Modal_AddrLine1" class="form-control" placeholder="Address and house number #1" aria-label="AddrLine1" aria-describedby="AddrLine1">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Address line 2</span>
                    <input type="text" disabled id="Modal_AddrLine2" class="form-control" placeholder="Address and house number #2" aria-label="AddrLine2" aria-describedby="AddrLine2">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">City</span>
                    <input type="text" disabled id="Modal_City" class="form-control" placeholder="City" aria-label="City" aria-describedby="City">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">State</span>
                    <input type="text" disabled id="Modal_State" class="form-control" placeholder="State" aria-label="State" aria-describedby="State">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Postal code</span>
                    <input type="text" disabled id="Modal_PostalCode" class="form-control" placeholder="Postal code" aria-label="PostalCode" aria-describedby="PostalCode">
                  </div>
                  <div class="input-group mb-3 w-100">
                    <span class="input-group-text">Country</span>
                    <input type="text" disabled id="Modal_Country" class="form-control" placeholder="Country" aria-label="Country" aria-describedby="Country">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editSectionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-w-auto">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><span id="secModalFunction"></span> section</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row pb-3">
            <div id='editSection' class="col-7 d-flex flex-column justify-content-center">
              <input type="hidden" id='secId'>
              <div class="input-group mb-3 w-100 ">
                <span class="input-group-text" id="SectionName">Name</span>
                <input type='text' maxlength="255" id="newSecName" required class="form-control" placeholder="Section name" aria-label="Section name" aria-describedby="SectionName">
              </div>

              <div class="input-group mb-3 w-100 ">
                <input type='file' id='newSecImage' accept='image/png, image/jpeg' class="form-control">
              </div>
            </div>
            <div class="col-5">
              Preview
              <div class="col-12" id="secPreview">
                <div class="section">
                  <p></p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn submit-editsec-btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editProductModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-w-auto">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><span id="prodModalFunction"></span> product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row pb-3">
            <div id='editProduct' class="col-7 d-flex flex-column justify-content-center">
              <input type="hidden" id="newProdID">
              <div class="input-group mb-3 w-100 ">
                <span class="input-group-text" id="ProdName">Name</span>
                <input type="text" id="newProdName" maxlength="255" class="form-control" placeholder="Name" aria-label="Name" aria-describedby="Name" required>
              </div>

              <div class="input-group mb-3 w-100 ">
                <span class="input-group-text" id="ProdDescription">Description</span>
                <input type="text" id="newProdDesc" class="form-control" placeholder="Description" aria-label="Description" aria-describedby="ProdDescription" required>
              </div>

              <div class="input-group mb-3 w-100 ">
                <span class="input-group-text" id="ProdStock">Stock</span>
                <input type="number" id="newProdStock" min="0" class="form-control" placeholder="Stock" aria-label="Stock" aria-describedby="ProdStock" required>
              </div>

              <div class="input-group mb-3 w-100 ">
                <span class="input-group-text" id="ProdPrice">Price</span>
                <input type="number" id="newProdPrice" min="0" class="form-control" placeholder="Price" aria-label="Price" aria-describedby="ProdPrice" required>
              </div>

              <div class="input-group mb-3 w-100 ">
                <input type="file" accept="image/png, image/jpeg" id="newProdImage" class="form-control">
              </div>

              <select id="editProdSec" class="form-select" aria-label="Select section" required>
              </select>
            </div>
            <div class="col-5">
              Preview
              <div class="col-12" id="prodPreview">
                <div class="card border-0 shadow h-100 product">
                  <span class="badge stock rounded-pill"></span>
                  <div class="bg-image">
                    <img src="" loading="lazy" class="card-img-top" alt="">
                  </div>
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title"></h5>
                    <p class="card-text" id="prodPreviewDesc"></p>
                    <div class="row align-items-center mt-auto">
                      <div class="col-8">
                        <p class="card-text cost"><span id="prodPreviewPrice"></span><a class="pts-logo absolute" title="Pts"></a></p>
                      </div>
                      <div class="col-4 d-flex justify-content-end">
                        <a class="btn btn-primary shadow-none">Buy</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn submit-editprod-btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="confirmDelModal" tabindex="-1" aria-labelledby="confirmDelModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-w-auto">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmDelModalLabel">Confirm deletion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="fs-6" id="confirmDelModalBody">Do you want to delete this section?</p>
          <div class="form-check" id="confDelCheckDiv">
            <input class="form-check-input" type="checkbox" value="" id="confDelCheck">
            <label class="form-check-label" for="flexCheckDefault">
              Delete also any product in this section
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-danger" id="confirmDelModalDelete">Delete</button>

        </div>
      </div>
    </div>
  </div>


  <script>
    /* ---------- GLOBAL VARIABLES AND ONLOAD ---------- */
    purchaseModal = null;
    editSectionModal = null;
    editProductModal = null;
    confirmDelModal = null;

    $(function() {
      editSectionModal = new bootstrap.Modal(document.getElementById('editSectionModal'), {});
      editProductModal = new bootstrap.Modal(document.getElementById('editProductModal'), {});
      purchaseModal = new bootstrap.Modal(document.getElementById('orderInfoModal'), {});
      confirmDelModal = new bootstrap.Modal(document.getElementById('confirmDelModal'));
      $('#createSection').hide()
      registerBtnListeners();
    });

    /* ---------- PURCHASES AREA ---------- */


    /* ---------- SECTIONS AREA ---------- */

    $('.opener-createsec-btn').click(function() {
      $('#secId').val(-1)
      $('#secModalFunction').text("New")
      $('#newSecName').val("")
      $("#newSecImage").val()
      $('#secPreview p').text("Section")
      $('#secPreview .section')[0].style.setProperty("--section-bg-url", "url('')")
      editSectionModal.show()
    })

    /*Register Listener for #editSectionForm submits*/
    $('.submit-editsec-btn').click(function(evt) {
      evt.preventDefault()
      fd = new FormData()
      fd.append('id', $('#secId').val())
      if ($('#secId').val() == -1) {
        fd.append('action', 'insert')
      } else {
        fd.append('action', 'update')
      }
      fd.append('name', $('#newSecName').val())
      if ($('#newSecImage').val()) {
        fd.append('image', $('#newSecImage').prop("files")[0])
      }
      $.ajax({
        method: 'POST',
        dataType: 'json',
        url: 'https://tiendagatito.com/api/sections',
        data: fd,
        processData: false,
        contentType: false,
        cache: false,
        success: function(response) {
          if (response.statusCode == 200) {
            editSectionModal.hide()
            refreshSections()
          }
          showToast(response.toast)
        }
      })
    })

    $("#newSecName").keydown(function(e) {
      $this = $(this)
      setTimeout(function() {
        if ($this.val().trim()) {
          $('#secPreview p').text($this.val())
        } else {
          $('#secPreview p').text("Section")
        }
      }, 10)
    })

    $("#newSecImage").change(function(evt) {
      var tgt = evt.target || window.event.srcElement,
        files = tgt.files
      if (FileReader && files && files.length) {
        var fr = new FileReader()
        fr.onload = function() {
          $('#secPreview .section')[0].style.setProperty("--section-bg-url", "url('" + fr.result + "')")
        }
        fr.readAsDataURL(files[0])
      }
    })

    /* ---------- PRODUCTS AREA ---------- */

    $('.opener-addproduct-btn').click(function() {
      $("#prodModalFunction").text("New")
      $('#editProdSec').empty()
      loadSectionsInSelect('#editProdSec')
      $("#newProdID").val(-1)
      $("#newProdName").val("")
      $("#newProdDesc").val("")
      $("#newProdStock").val("")
      $("#newProdPrice").val("")
      $("#newProdImage").val("")
      $("#prodPreview .badge").text("").removeClass("bg-danger bg-secondary").addClass("d-none")
      $("#prodPreview img").attr("src", "/assets/imgs/prodImagePreview.jpg")
      $("#prodPreview .card-title").text("Name")
      $("#prodPreviewDesc").text("Description")
      $("#prodPreviewPrice").text("Price")
      editProductModal.show()
    })

    $('.submit-editprod-btn').click(function(evt) {
      evt.preventDefault()
      fd = new FormData();
      if ($('#newProdID').val() == -1) {
        fd.append('action', 'insert')
      } else {
        fd.append('action', 'update')
      }
      fd.append('id', $("#newProdID").val())
      fd.append('name', $("#newProdName").val())
      fd.append('description', $("#newProdDesc").val())
      fd.append('stock', $("#newProdStock").val())
      fd.append('price', $("#newProdPrice").val())
      if ($('#newProdImage').val()) {
        fd.append('image', $('#newProdImage').prop('files')[0])
      }
      fd.append('section', $("#editProdSec").val())
      $.ajax({
        method: 'POST',
        dataType: 'json',
        url: 'https://tiendagatito.com/api/products',
        data: fd,
        processData: false,
        contentType: false,
        cache: false,
        success: function(response) {
          if (response.statusCode == 200) {
            editProductModal.hide()
            refreshProducts()
          }
          showToast(response.toast)
        }
      })
    })

    $("#newProdName").keydown(function(e) {
      $this = $(this)
      setTimeout(function() {
        if ($this.val().trim()) {
          $('#prodPreview .card-title').text($this.val())
        } else {
          $('#prodPreview .card-title').text("Name")
        }
      }, 10)
    })

    $("#newProdDesc").keydown(function(e) {
      $this = $(this)
      setTimeout(function() {
        if ($this.val().trim()) {
          $('#prodPreviewDesc').text($this.val())
        } else {
          $('#prodPreviewDesc').text("Description")
        }
      }, 10)
    })

    $("#newProdStock").keydown(function(e) {
      $this = $(this)
      setTimeout(function() {
        if ($this.val().trim()) {
          $("#prodPreview .badge").text("").removeClass("bg-danger bg-secondary d-none").addClass("")
          if ($this.val() <= 10 && $this.val() > 0) {
            $("#prodPreview .badge").text($this.val() + " in stock!").addClass("bg-danger")
          } else if ($this.val() == 0) {
            $("#prodPreview .badge").text("Out of stock").addClass("bg-secondary")
          } else {
            $("#prodPreview .badge").text("").removeClass("bg-danger bg-secondary").addClass("d-none")
          }
        } else {
          $("#prodPreview .badge").text("").removeClass("bg-danger bg-secondary").addClass("d-none")
        }
      }, 10)
    })

    $("#newProdPrice").keydown(function(e) {
      $this = $(this)
      setTimeout(function() {
        if ($this.val().trim()) {
          $('#prodPreviewPrice').text($this.val())
        } else {
          $('#prodPreviewPrice').text("Price")
        }
      }, 10)
    })

    $("#newProdImage").change(function(evt) {
      var tgt = evt.target || window.event.srcElement,
        files = tgt.files
      if (FileReader && files && files.length) {
        var fr = new FileReader()
        fr.onload = function() {
          $('#prodPreview img').attr("src", fr.result)
        }
        fr.readAsDataURL(files[0])
      }
    })

    /* ---------- SEASON AREA ---------- */

    $(".resetseason-btn").click(function() {
      $.ajax({
        url: 'https://tiendagatito.com/api/season',
        method: 'DELETE',
        dataType: 'json',
        success: function(data) {
          showToast(data.toast)
        }
      })
    })

    $('.setseasondetails-btn').click(function() {
      fd = new FormData()
      fd.append('number', $('#seasonNumber').val())
      fd.append('name', $('#seasonName').val())
      fd.append('end', $('#seasonEnd').val())
      $.ajax({
        url: 'https://tiendagatito.com/api/season',
        method: 'POST',
        data: fd,
        dataType: 'json',
        processData: false, 
        contentType: false,
        success: function(data) {
          showToast(data.toast)
        }
      })
    })

    /* ---------- REFRESH AND GENERAL FUNCTIONS ---------- */

    function showConfDel(body, data, actionOnDel) {
      $("#confirmDelModalBody").text(body);
      if (!data.hasOwnProperty("showConfDelCheck")) {
        data.showConfDelCheck = false
      }
      if (data.showConfDelCheck) {
        $("#confDelCheckDiv").show()
        $("#confDelCheck").prop("checked", false)
      } else {
        $("#confDelCheckDiv").hide()
      }
      $("#confirmDelModalDelete").off("click").click(function() {
        actionOnDel(data)
        confirmDelModal.hide()
      });
      confirmDelModal.show()
    }

    function loadSectionsInSelect(selectId, selection = '-1') {
      $.get('https://tiendagatito.com/api/sections', function(data) {
        data = data.data
        $(selectId).html("<option value='' disabled selected>Select a section</option>");
        for (i = 0; i < data.length; i++) {
          $(selectId).append("<option value='" + data[i].id + "' " + (selection == data[i].id ? "selected" : "") + ">" + data[i].id + " | " + data[i].name + "</option>")
        }
      })
    }

    function refreshPurchases() {
      $.ajax({
        method: 'GET',
        url: "https://tiendagatito.com/api/purchases",
        dataType: 'json',
        data: {
          type: 'includedetails'
        },
        success: function(response) {
          rowsHtml = "";
          $.each(response.data, function(index, element) {
            rowsHtml += `<tr>
                          <th scope="row">` + element.idpur + `</th>
                          <td>` + element.prodname + `</td>
                          <td>` + element.twitchid + `</td>
                          <td><button class='btn table-moreinfopur-btn btn-primary' purchaseID='` + element.idpur + `'>More info</button></td>
                        </tr>`
          })
          $("#purchasesTbody").html(rowsHtml);
          registerBtnListeners();
        }
      })
    }

    function refreshSections() {
      $.ajax({
        method: 'GET',
        url: "https://tiendagatito.com/api/sections",
        dataType: 'json',
        success: function(response) {
          rowsHtml = "";
          $.each(response.data, function(index, element) {
            rowsHtml += `<tr>
                           <th scope="row">` + element.id + `</th>
                           <td>` + element.name + `</td>
                           <td><button class='btn table-editsec-btn btn-primary' sectionID='` + element.id + `'>Edit</button></td>
                           <td><button class='btn table-deletesec-btn btn-danger' sectionID='` + element.id + `'>Delete</button></td>
                          </tr>`
          })
          $("#sectionsTbody").html(rowsHtml);
          registerBtnListeners();
        }
      })
    }

    function refreshProducts() {
      $.ajax({
        method: 'GET',
        url: "https://tiendagatito.com/api/products",
        dataType: 'json',
        success: function(response) {
          rowsHtml = "";
          $.each(response.data, function(index, element) {
            rowsHtml += `<tr>
                          <th scope="row">` + element.id + `</th>
                          <td>` + element.name + `</td>
                          <td>` + element.price + `</td>
                          <td><button class='btn table-editprod-btn btn-primary' productID='` + element.id + `'>Edit</button></td>
                          <td><button class='btn table-deleteprod-btn btn-danger' productID='` + element.id + `'>Delete</button></td>
                        </tr>`
          })
          $("#productsTbody").html()
          $("#productsTbody").html(rowsHtml);
          registerBtnListeners();
        }
      })
    }

    $(window).on('hashchange', function(e) {
      history.replaceState("", document.title, e.originalEvent.oldURL);
    });

    /* Nella funzione sotto mettere SOLO i listeners dei pulsanti delle 3 tabelle */
    function registerBtnListeners() {
      /*Register Listener for .table-moreinfopur-btn buttons*/
      $('.table-moreinfopur-btn').click(function() {
        const purchaseID = $(this).attr('purchaseID')
        $.get('https://tiendagatito.com/api/purchases?id=' + purchaseID, function(data) {
          $.ajax({
            url: "https://api.twitch.tv/helix/users?id=" + data.data.user,
            headers: {
              'Authorization': 'Bearer <?= $accessToken ?>',
              'Client-ID': 'pb8w32i9zo32mcn6m768qjpd3ic1vr'
            },
            success: function(response) {
              $("#Modal_DisplayName").val(response.data[0].display_name)
            }
          })
          var od = data.data
          $("#Modal_ProductID").val(od.product)
          $("#Modal_ProductName").val(od.prodName)
          $("#Modal_Description").val(od.prodDesc)
          $("#Modal_Price").val(od.prodPrice)
          $("#Modal_TwitchID").val(od.user)
          $("#Modal_FullName").val(od.fullName)
          $("#Modal_Email").val(od.email)
          $("#Modal_PhoneNumber").val(od.phoneNumber)
          $("#Modal_AddrLine1").val(od.addressOne)
          $("#Modal_AddrLine2").val(od.addressTwo)
          $("#Modal_City").val(od.city)
          $("#Modal_State").val(od.state)
          $("#Modal_PostalCode").val(od.postalCode)
          $("#Modal_Country").val(od.country)
          $("#Modal_Date").val(od.purchase_datetime)
          $("#Modal_OrderNum").val(purchaseID)
          purchaseModal.show();
        })
      });

      /*Register Listener for .table-editsec-btn buttons*/
      $('.table-editsec-btn').click(function() {
        const sectionID = $(this).attr('sectionID')
        $('#secModalFunction').text("Edit")
        $.get('https://tiendagatito.com/api/sections?id=' + sectionID, function(data) {
          $('#secId').val(sectionID)
          sectionData = data.data
          $('#newSecName').val(sectionData.name)
          $("#newSecImage").val()
          $('#secPreview p').text(sectionData.name)
          $('#secPreview .section')[0].style.setProperty("--section-bg-url", "url('" + sectionData.image + "')")
          editSectionModal.show()
        })
      });

      /*Register Listener for .table-deletesec-btn buttons*/
      $('.table-deletesec-btn').click(function() {
        showConfDel("Do you want to delete this section?", {
          sectionID: $(this).attr('sectionID'),
          showConfDelCheck: true
        }, function(data) {
          const sectionID = data.sectionID;
          $.ajax({
            method: 'DELETE',
            dataType: 'json',
            url: 'https://tiendagatito.com/api/sections?id=' + sectionID,
            data: {
              force_delete: $("#confDelCheck").is(":checked")
            },
            success: function(asd) {
              showToast(asd.toast)
              refreshSections()
            }
          })
        })
      })

      /*Register Listener for .table-editprod-btn buttons*/
      $('.table-editprod-btn').click(function() {
        $("#prodModalFunction").text("Edit")
        const productID = $(this).attr('productID')
        const sectionSelect = $('#editProdSec')
        sectionSelect.empty()
        $.get('https://tiendagatito.com/api/products?id=' + productID, function(data) {
          productData = data.data
          previousSec = productData.section
          $("#newProdID").val(productID)
          $("#newProdName").val(productData.name)
          $("#newProdDesc").val(productData.description)
          $("#newProdStock").val(productData.stock)
          $("#newProdPrice").val(productData.price)
          $("#newProdImage").val("")
          loadSectionsInSelect('#editProdSec', previousSec)
          $("#prodPreview .badge").text("").removeClass("bg-danger bg-secondary d-none").addClass("")
          if (productData.stock <= 10 && productData.stock > 0) {
            $("#prodPreview .badge").text(productData.stock + " in stock!").addClass("bg-danger")
          } else if (productData.stock == 0) {
            $("#prodPreview .badge").text("Out of stock").addClass("bg-secondary")
          }
          $("#prodPreview img").attr("src", productData.image)
          $("#prodPreview .card-title").text(productData.name)
          $("#prodPreviewDesc").text(productData.description)
          $("#prodPreviewPrice").text(productData.price)
          editProductModal.show()
        })
      })

      /*Register Listener for .table-deleteprod-btn buttons*/
      $('.table-deleteprod-btn').click(function() {
        showConfDel("Do you want to delete this product?", {
          productID: $(this).attr('productID')
        }, function(data) {
          const productID = data.productID
          $.ajax({
            method: 'DELETE',
            dataType: 'json',
            url: 'https://tiendagatito.com/api/products?id=' + productID,
            success: function(asd) {
              showToast(asd.toast)
              refreshProducts()
            }
          })
        })
      })
    }
  </script>
  <?php printFooter(); ?>
</body>

</html>