<?php
/*
* StorageGRID Webscale Accounting
*
* Listing of tenants' accounts quotas and usage
*
* PHP version 7
*
* @author Adly Taibi
* @version 1.0
*/
include 'vendor/nategood/httpful/bootstrap.php';
function uwrap($hostname) {
  return "https://".$hostname."/api/v2";
}

# Endpoint checks and grabs

if (isset($_SESSION['endpoint'])) {
  $uri = uwrap($_SESSION['endpoint']);
} else {
  # Read endpoint from file
  if (file_exists("endpoint")) {
    $api = fopen("endpoint", "r") or print "<div class='alert alert-warning' role='alert'>Error: reading endpoint file.</div>";
    $epfromfile = fgets($api);
    $_SESSION['endpoint'] = $epfromfile;
    $uri = uwrap($epfromfile);
    fclose($api);
  } else {
    print "<div class='alert alert-warning' role='alert'>Please, set endpoint.</div>";
  }
}

# User interaction

function postdata($uri) {
  if ($_POST['btn']=='login') {
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['password'] = $_POST['password'];
  }
  if ($_POST['btn']=='save') {
    $_SESSION['endpoint'] = $_POST['endpoint'];
    $uri = uwrap($_POST['endpoint']);
    # Remember and save the endpoint to file
    $api = fopen("endpoint", "w") or print "<div class='alert alert-warning' role='alert'>Error: writing endpoint file.</div>";
    fwrite($api, $_POST['endpoint']);
    fclose($api);
    header('location: .');
  }
  if ($_POST['btn']=='logout') {
    # Remove authorization, unset session variables and destroy session
    $response = \Httpful\Request::delete($uri.'/authorize')->send();
    session_unset();
    session_destroy();
    header('location: .');
  }
}

# Units

function usize($size, $type=1) {
  $unit = ($type==1)?1024:1000;
  for($i = 0; ($size / $unit) > 0.9; $i++, $size /= $unit) {}
  return round($size, [0,0,1,2,2,3,3,4,4][$i]).' '.['B','kB','MB','GB','TB','PB','EB','ZB','YB'][$i];
}

# Logout button

function logoutbtn() {
  print '<!-- Logout button -->
<form action=. method=post>
  <div class="form-logout">
  <span class="group-btn">     
    <button type="submit" name="btn" class="btn btn-primary btn-md" value="logout">logout <i class="fa fa-sign-out"></i></button>
  </span>
  </div>
</form>';
}

# The core of getting the data

function firstlogin($uri) {
  # Get a token for the first time login
  if (isset($_SESSION['username']) and isset($_SESSION['password'])) {
    if (!empty($uri)) {
      $username = $_SESSION['username'];
      $password = $_SESSION['password'];
      try {
        $response = \Httpful\Request::post($uri.'/authorize')
                  ->body('{ "username": "'.$username.'", "password": "'.$password.'", "cookie": false, "csrfToken": false }')
                  ->send();
        $status = $response->code;
        if ($status!=200 || isset($_SESSION['token'])) {
          print "<div class='alert alert-warning' role='alert'>Invalid login ".$status."</div>";
        } else {
          $_SESSION['token'] = $response->body->data;
          $_SESSION['success'] = 1;
          header('location: .');
        }
      }
      catch(Exception $e) {
        print "<div class='alert alert-warning' role='alert'>Error: ".$e->getMessage()."</div>";
      } 
    } else {
      print "<div class='alert alert-warning' role='alert'>Error: Endpoint is not set.</div>";
    }
  }
}

function nextlogins($uri) {
  # Re-use token
  $token = $_SESSION['token'];
  $marker = '';
  $rows = '';
  $totquotas = 0;
  $totused = 0;
  $AccountsTable = "<table id='data' class='display'><thead><tr><th>Tenant</th><th>Quota Size</th><th>Size Used</th></tr></thead><tbody>";
  while (1) {
    $response = \Httpful\Request::get($uri.'/grid/accounts?limit=20&marker='.$marker)
                ->addHeader('Authorization', $token)
                ->send();
    if (property_exists($response->body, 'data')) {
      if (count($response->body->data)==0) {
        break;
      }
      foreach ($response->body->data as $account) {
        $id = $account->id;
        $name = $account->name;
        $quota = $account->policy->quotaObjectBytes;
        $response = \Httpful\Request::get($uri.'/grid/accounts/'.$account->id.'/usage')
                    ->addHeader('Authorization', $token)
                    ->send();
        $databytes = $response->body->data->dataBytes;
        $rows .= "<tr><td data-sort='{$name}'>{$name}</td><td data-sort='{$quota}'>".usize($quota,0)."</td><td data-sort='{$databytes}'>".usize($databytes,0)."</td></tr>";
        $totquotas += $quota;
        $totused += $databytes;
      }
      $marker = $id; # Use the last accountId as a marker for a possible pagination
    }
  }
  $tots = "<tr><td data-sort='0'>All Tenants</td><td data-sort='{$totquotas}'>".usize($totquotas,0)."</td><td data-sort='{$totused}'>".usize($totused,0)."</td></tr>";
  $AccountsTable .= $tots.$rows;
  $AccountsTable .= '</tbody></table>';
  print $AccountsTable;
}

# Login form

function loginform($uri) {
  $endpoint = (isset($_SESSION['endpoint']))?$_SESSION['endpoint']:'';
  print '<!-- Login form -->
<div class="container">
  <div class="row">
    <div class="col-sm-offset-3 col-sm-6">
      <form action=. method=post>
        <div class="form-login">
        <h4>Welcome</h4>
        <input type="text" name="username" class="form-control input-sm chat-input" placeholder="username" required />
        </br>
        <input type="password" name="password" class="form-control input-sm chat-input" placeholder="password" required />
        </br>
        <div class="wrapper">
          <span class="group-btn">     
            <button type="submit" name="btn" class="btn btn-primary btn-md" value="login">login <i class="fa fa-sign-in"></i></button>
          </span>
        </div>
        <div class="wrapper">
          <span class="group-btn badge badge-pill badge-info">API Endpoint: '.$uri.'</span>
          <button type="button" name="btn" class="btn btn-primary btn-md" data-toggle="modal" data-target="#setting"><i class="fa fa-cog"></i></button>
        </div>
        </div>
      </form>
    </div>
  </div> <!-- row -->
</div> <!-- container -->
<form action=. method=post>
  <div class="modal fade" id="setting" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="Setting">Endpoint</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><i class="fa fa-close"></i></span>
          </button>
        </div>
        <div class="modal-body">
          <input name="endpoint" class="form-control input-sm chat-input" value="'.$endpoint.'" placeholder="endpoint hostname or IP address" pattern="^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$" required />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" name="btn" class="btn btn-primary" value="save">Save changes</button>
        </div>
      </div>
    </div>
  </div>
</form>';
}
?>
