<?php include("header.php");

//Paging
if(isset($_GET['p'])){
  $p = intval($_GET['p']);
  $record_from = (($p)-1)*$_SESSION['records_per_page'];
  $record_to = $_SESSION['records_per_page'];
}else{
  $record_from = 0;
  $record_to = $_SESSION['records_per_page'];
  $p = 1;
}
  
if(isset($_GET['q'])){
  $q = mysqli_real_escape_string($mysqli,$_GET['q']);
}else{
  $q = "";
}

if(!empty($_GET['sb'])){
  $sb = mysqli_real_escape_string($mysqli,$_GET['sb']);
}else{
  $sb = "log_id";
}

if(isset($_GET['o'])){
  if($_GET['o'] == 'ASC'){
    $o = "ASC";
    $disp = "DESC";
  }else{
    $o = "DESC";
    $disp = "ASC";
  }
}else{
  $o = "DESC";
  $disp = "ASC";
}

//Date Filter
if($_GET['canned_date'] == "custom" AND !empty($_GET['dtf'])){
  $dtf = $_GET['dtf'];
  $dtt = $_GET['dtt'];
}elseif($_GET['canned_date'] == "today"){
  $dtf = date('Y-m-d');
  $dtt = date('Y-m-d');
}elseif($_GET['canned_date'] == "yesterday"){
  $dtf = date('Y-m-d',strtotime("yesterday"));
  $dtt = date('Y-m-d',strtotime("yesterday"));
}elseif($_GET['canned_date'] == "thisweek"){
  $dtf = date('Y-m-d',strtotime("monday this week"));
  $dtt = date('Y-m-d');
}elseif($_GET['canned_date'] == "lastweek"){
  $dtf = date('Y-m-d',strtotime("monday last week"));
  $dtt = date('Y-m-d',strtotime("sunday last week"));
}elseif($_GET['canned_date'] == "thismonth"){
  $dtf = date('Y-m-01');
  $dtt = date('Y-m-d');
}elseif($_GET['canned_date'] == "lastmonth"){
  $dtf = date('Y-m-d',strtotime("first day of last month"));
  $dtt = date('Y-m-d',strtotime("last day of last month"));
}elseif($_GET['canned_date'] == "thisyear"){
  $dtf = date('Y-01-01');
  $dtt = date('Y-m-d');
}elseif($_GET['canned_date'] == "lastyear"){
  $dtf = date('Y-m-d',strtotime("first day of january last year"));
  $dtt = date('Y-m-d',strtotime("last day of december last year"));  
}else{
  $dtf = "0000-00-00";
  $dtt = "9999-00-00";
}

//Rebuild URL
$url_query_strings_sb = http_build_query(array_merge($_GET,array('sb' => $sb, 'o' => $o)));

$sql = mysqli_query($mysqli,"SELECT SQL_CALC_FOUND_ROWS * FROM logs
  LEFT JOIN users ON log_user_id = user_id
  WHERE (log_type LIKE '%$q%' OR log_action LIKE '%$q%' OR log_description LIKE '%$q%' OR user_name LIKE '%$q%')
  AND DATE(log_created_at) BETWEEN '$dtf' AND '$dtt'
  ORDER BY $sb $o LIMIT $record_from, $record_to"
);

$num_rows = mysqli_fetch_row(mysqli_query($mysqli,"SELECT FOUND_ROWS()"));

?>

<div class="card card-dark">
  <div class="card-header">
    <h3 class="card-title"><i class="fa fa-fw fa-book"></i> Logs <?php echo $extended_query; ?></h3>
  </div>
  <div class="card-body">
    <form class="mb-4" autocomplete="off">
      <div class="row">
        <div class="col-sm-4">
          <div class="input-group">
            <input type="search" class="form-control" name="q" value="<?php if(isset($q)){echo stripslashes($q);} ?>" placeholder="Search Logs">
            <div class="input-group-append">
              <button class="btn btn-secondary" type="button" data-toggle="collapse" data-target="#advancedFilter"><i class="fas fa-filter"></i></button>
              <button class="btn btn-primary"><i class="fa fa-search"></i></button>
            </div>
          </div>
        </div>
      </div>
      <div class="collapse mt-3 <?php if(!empty($_GET['dtf'])){ echo "show"; } ?>" id="advancedFilter">
        <div class="row">
          <div class="col-md-2">
            <div class="form-group">
              <label>Canned Date</label>
              <select class="form-control select2" name="canned_date">
                <option <?php if($_GET['canned_date'] == "custom"){ echo "selected"; } ?> value="">Custom</option>
                <option <?php if($_GET['canned_date'] == "today"){ echo "selected"; } ?> value="today">Today</option>
                <option <?php if($_GET['canned_date'] == "yesterday"){ echo "selected"; } ?> value="yesterday">Yesterday</option>
                <option <?php if($_GET['canned_date'] == "thisweek"){ echo "selected"; } ?> value="thisweek">This Week</option>
                <option <?php if($_GET['canned_date'] == "lastweek"){ echo "selected"; } ?> value="lastweek">Last Week</option>
                <option <?php if($_GET['canned_date'] == "thismonth"){ echo "selected"; } ?> value="thismonth">This Month</option>
                <option <?php if($_GET['canned_date'] == "lastmonth"){ echo "selected"; } ?> value="lastmonth">Last Month</option>
                <option <?php if($_GET['canned_date'] == "thisyear"){ echo "selected"; } ?> value="thisyear">This Year</option>
                <option <?php if($_GET['canned_date'] == "lastyear"){ echo "selected"; } ?> value="lastyear">Last Year</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Date From</label>
              <input type="date" class="form-control" name="dtf" value="<?php echo $dtf; ?>">
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Date To</label>
              <input type="date" class="form-control" name="dtt" value="<?php echo $dtt; ?>">
            </div>
          </div>
        </div>    
      </div>
    </form>
    <hr>
    <div class="table-responsive">
      <table class="table table-striped table-borderless table-hover">
        <thead class="text-dark <?php if($num_rows[0] == 0){ echo "d-none"; } ?>">
          <tr>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=log_created_at&o=<?php echo $disp; ?>">Timestamp</a></th>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=user_name&o=<?php echo $disp; ?>">User</a></th>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=log_type&o=<?php echo $disp; ?>">Type</a></th>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=log_action&o=<?php echo $disp; ?>">Action</a></th>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=log_description&o=<?php echo $disp; ?>">Description</a></th>
          </tr>
        </thead>
        <tbody>
          <?php
      
          while($row = mysqli_fetch_array($sql)){
            $log_id = $row['log_id'];
            $log_type = $row['log_type'];
            $log_action = $row['log_action'];
            $log_description = $row['log_description'];
            $log_created_at = $row['log_created_at'];
            $user_id = $row['user_id'];
            $user_name = $row['user_name'];
            if(empty($user_name)){
              $user_name_display = "-";
            }else{
              $user_name_display = $user_name;
            }
          
          ?>
          
          <tr>
            <td><?php echo $log_created_at; ?></td>
            <td><?php echo $user_name_display; ?></td>
            <td><?php echo $log_type; ?></td>
            <td><?php echo $log_action; ?></td>
            <td><?php echo $log_description; ?></td>
          </tr>

          <?php
          }
          ?>

        </tbody>
      </table>
    </div>
    <?php include("pagination.php"); ?>
  </div>
</div>

<?php include("footer.php");