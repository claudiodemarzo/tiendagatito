<?php

function printHead($pageName = "Tienda Gatito")
{
  if ($_SERVER['REQUEST_URI'] != "/404") {
    $_SESSION['currentURI'] = $_SERVER['REQUEST_URI'];
  } else {
    unset($_SESSION['currentURI']);
  }

  echo <<<HTML
  <head>
    <meta charset="utf-8">
    <title>$pageName</title>
    <script src="/js/jquery-3.6.0-min.js" charset="utf-8"></script>
    <script defer src="/js/common.js" charset="utf-8"></script>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon.png">
    <link rel="shortcut icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">
    <link rel="manifest" href="/assets/icons/site.webmanifest">
    <link rel="mask-icon" href="/assets/icons/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="/assets/icons/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="/assets/icons/browserconfig.xml">
    <meta name="theme-color" content="#1A3452 ">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  </head>
  HTML;
}

function printNavbar($pageName, $link)
{
  include 'db.php';
  echo '
  <nav class="navbar navbar-expand-lg ' . (($pageName == "Account" || $pageName == "Admin Panel" || $pageName == "Product") ? "sticky-top" : "fixed-top") . ' navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="/">
         <img src="/assets/imgs/logo.png" alt="" width="35" height="35">
      </a>
      <div class="d-flex flex-row">
      ' . ($pageName == "Catalog" ? '<button type="button" class="btn btn-primary mobile-display-only me-2" data-bs-toggle="modal" data-bs-target="#filtersPopup">
      <span class="material-icons buttonIcon" style="font-size: 19px">filter_list</span>Filters
    </button>' : "") . '
      <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      </div>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav mr-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link ' . (($pageName == "Home") ? 'active" aria-current="page' : '') . '" href="/">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link ' . (($pageName == "Catalog") ? 'active" aria-current="page' : '') . '" href="/catalog">Catalogo</a>
          </li>
        </ul>';
  $getSeasonq = mysqli_query($link, "select *,now() now from season");
  if ($getSeasonq) {
    $season = mysqli_fetch_assoc($getSeasonq);
    echo "<li class='navbar-nav navbar-countdown align-middle'><p class='text-white m-0 p-0 countDownSeason' data-num='".$season['number']."' data-now='" . $season['now'] . "' data-timeend='" . $season['end'] . "'></p></li>";
  }

  echo '<ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center mobile-row">';
  if (isset($_SESSION['loginStatus']) && $_SESSION['loginStatus']) {
    $userdata = $_SESSION['data'];
    $pts = mysqli_query($link, "SELECT points FROM users WHERE twitchID='$userdata->id'");
    if (!$pts) {
      $pts = "???";
    } else {
      $pts = mysqli_fetch_array($pts)['points'];
      if (is_null($pts)) {
        $pts = 0;
      }
    }
?>
    <div class="nav-pts mx-2">
      <div class="amount" aria-label="Amount"><?= $pts ?></div>
      <div class="icon"><a class="pts-logo" title="Pts"></a></div>
    </div>
    <div class="dropdown mx-2">
      <button class="btn btn-secondary dropdown-toggle shadow-none" type="button" id="profileDropdown" data-bs-toggle="dropdown" data-bs-offset="0,10" aria-haspopup="true" aria-expanded="false">
        <?= $userdata->display_name ?>
      </button>
      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
        <a class="dropdown-item <?= ($pageName == "Account") ? 'active" aria-current="page' : '' ?>" href="/account">Cuenta</a>
        <?php
        if (in_array($_SESSION['data']->id, $adminIDs)) {
          echo '<a class="dropdown-item ' . ($pageName == "Admin Panel" ? "active\"" : "\"") . 'href="/admin">Admin</a>';
        }
        ?>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="/logout">Salir</a>
      </div>
    </div>
    <img src="<?= $userdata->profile_image_url ?>" alt="profile picture" class="prof-pic">
  <?php
  } else {
  ?>
    <li class="nav-item"><a href="/twitchlogin" class="btn twitch-login text-white shadow-none" style="float:right;font-size:12px"><img src="/assets/imgs/twitch-icon.png" style="width:16px;border-radius:0;margin-right:8px;">Ingresar con Twitch</a></li>
    <?php

  };
  echo "</ul>
        </div>
      </div>
    </nav>";
}

function printFooter($toast = true)
{
  if ($toast) {
    if (isset($_SESSION['toast'])) {
    ?>
      <script type="text/javascript">
        $(function() {
          <?php
          foreach ($_SESSION['toast'] as $toast) {
          ?>
            showToast(<?= json_encode($toast) ?>);
          <?php
          }
          unset($_SESSION['toast']);
          ?>
        });
      </script>
<?php
    }
    echo <<<HTML
    <script src="/js/toast.js" charset="utf-8"></script>
    HTML;
  }
  echo <<<HTML
  <footer class="footer py-3 mt-auto bg-dark">
    <div class="container">
      <div class="row mobile-col">
        <div class="col text-center"><span class="text-muted">© <?=date("Y") ?> Copyright NerB Studio. All Rights Reserved</span></div>
        <div class="col text-center"><span class="text-muted">Made by NerB Studio</span></div>
      </div>
    </div>
  </footer>
  <script src='/js/bootstrap.bundle.min.js'></script>
  HTML;
}

function checkLogin($redirect)
{
  $accessToken = $_SESSION['data']->accessToken;
  $curl = curl_init("https://id.twitch.tv/oauth2/validate");

  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $headers = array();
  $headers[0] = 'Authorization: Bearer ' . $accessToken;
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

  $api_response = curl_exec($curl);
  curl_close($curl);
  $api_response = json_decode($api_response, true);
  if (isset($api_response['status'])) {
    $_SESSION['redirect'] = $redirect;
    header("location: /twitchlogin");
    die();
  }
}
?>