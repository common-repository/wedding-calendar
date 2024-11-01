<?php
/**
 * Plugin Name: Indian wedding calendar
 * Plugin URI: 
 * Description: This plugin will display Indian wedding calendar using Shortcode.
 * Version: 1.5
 * Author: Avii R.
 * Author URI: 
 */

$value_string=get_option('inwedcal_wedding_dates');

$holiday_list = preg_split('/[\n\r]+/', $value_string);

/* Setting Page Code started */

function inwedcal_register_settings() {
    add_option( 'inwedcal_wedding_dates', 'Wedding Dates');
    register_setting( 'inwedcal_wedding_date_options_group', 'inwedcal_wedding_dates', 'inwedcal_callback' );
}
add_action( 'admin_init', 'inwedcal_register_settings' );

function inwedcal_register_options_page() {
    add_options_page('Wedding Calendar Settings', 'Wedding Calendar Settings', 'manage_options', 'inwedcal', 'inwedcal_options_page');
}
add_action('admin_menu', 'inwedcal_register_options_page');

function inwedcal_options_page() {
?>
	<div id="inwedcal-settings">
		<h1>Wedding Calendar Settings Page</h1>
		<p>Place this shortcode to display calendar: [display_calendar] </p>
		<form method="post" action="options.php">
			<?php settings_fields( 'inwedcal_wedding_date_options_group' ); ?>
			<h2>Wedding Calendar Dates:</h2>
			<p>Enter each new date in new line as one date per line and in dd-mm-yy format. 6th february in 2021 wil be 06-02-2021.</p>
			<table>
				<tr valign="top">
					<th scope="row"><label for="inwedcal_wedding_dates">Date</label></th>
				</tr>
				<tr>
					<td>
						<textarea id="inwedcal_wedding_dates" name="inwedcal_wedding_dates" rows="10" cols="50"  ><?php echo get_option('inwedcal_wedding_dates'); ?></textarea>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
  </div>
<?php
}

/* Setting page code Over */

function inwedcal_enqueue_func() {
      wp_enqueue_style( 'inwedcal-style', plugins_url( '/css/style.css', __FILE__ ) );
	  wp_enqueue_script( 'inwedcal-script', plugins_url( '/js/inwedcal.js', __FILE__ ), 'jQuery', '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'inwedcal_enqueue_func' );
 
function inwedcal_get_month_start_days($week,$year,$first_day,$offset) {
    $gout="";
    $firstday= date('Y-m-d', strtotime($first_day .' -1 day'));
    for($i=$offset;$i>=1;$i--){
        $firstday= date('d', strtotime($first_day .' -'.$i.' day'));
        $gout.="<td class='bgg'>".$firstday."</td>";
    }
    return $gout;
}

function inwedcal_showCurrentMonth($current_Month, $year) {
    $date = mktime(12, 0, 0, $current_Month, 1, $year);
    $numberOfDays =cal_days_in_month(CAL_GREGORIAN,$current_Month, $year);
    $today_date = date('d', time());
    $current_month_name = date('F / Y', $date);
    $offset = date("w", $date);
    $flag=0;
    if($current_Month==date('m') && $year==date('Y')) {
        $flag=1;
    }
    $row_number = 1;
   
    $first_day= date('Y-m-01', $date);
    $date1 = new DateTime($first_day);
    $week = $date1->format("W");
    global $holiday_list;
    $out = "";
   // $out.= "<h2>Calendar of ".$current_month_name."</h2>";
    
    $out.= "<table id='cmonthly_cal'><br/>";
        $out.= "<tr><td>Sun</td><td>Mon</td><td>Tue</td><td>Wed</td><td>Thu</td><td>Fri</td><td>Sat</td></tr> <tr>";
        $out.= inwedcal_get_month_start_days($week,$year,$first_day,$offset);
        for($day = 1; $day <= $numberOfDays; $day++) {
            
            if( ($day + $offset - 1) % 7 == 0 && $day != 1) {
                $out.= "</tr> <tr>";
                $row_number++;
            }
            $current_dayn=date('D', strtotime($first_day .' +'.($day-1).' day'));
            $check_date=date('d-m-Y', strtotime($first_day .' +'.($day-1).' day'));
           
            if(in_array($check_date, $holiday_list)) {
                $current_date=date('d', strtotime($first_day .' +'.($day-1).' day'));
                $out.= "<td class='bgr'>" . $current_date."</td>";
            }
            else{
                if($flag==1){
                    if($day == $today_date){
                        $current_date=date('d', strtotime($first_day .' +'.($day-1).' day'));
                        $out.= "<td class='today'>" . $current_date . "</td>";
                    }
                    else {
                        $current_date=date('d', strtotime($first_day .' +'.($day-1).' day'));
                        $out.= "<td class='bgw'>" . $current_date . "</td>";    
                    }
                }
                else
                {
                    $current_date=date('d', strtotime($first_day .' +'.($day-1).' day'));
                    $out.= "<td class='bgw'>" . $current_date . "</td>";
                }
            }
        }
    
        $i=1;
        while( ($day + $offset) <= $row_number * 7) {
            $out.= "<td class='bgg'>".$i."</td>";
            $i++;
            $day++;
    }
    $out.= "</tr></table>";
	$out.='<p>Dates marked in red are wedding dates.</p>';
	return $out;
}
function inwedcal_display_cal() { 
	$out = "";
	$current_month=date('m');
	$current_year=date('Y');
	
	if(isset($_POST['month_number']))
	{
		$current_month=sanitize_text_field($_POST['month_number']);
	}
	
	if(isset($_POST['year_number']))
	{
	   $current_year = sanitize_text_field($_POST['year_number']);
	}
	
	$out.= '<div id="inwedcal-forms-nav">';
	
		$out.= "<form id='inwedcal_calformprev' class='inwedcal_calforms' action='' method='POST'>";
			$out.= "<div class='inwedcal_inputfield'>";
		
				$out.= '<input type="hidden" name="month_number" value='.($current_month-1).'>';
			
				$out.= "<a id='month_number_prev' class='inwedcal_nav_item' href='#' onClick='inwedcal_submitFormPrev();' ><</a>";
		
			$out.= "</div>";
		
		$out.= "</form>";
		
		$out.= "<form id='inwedcal_calform' class='inwedcal_calforms' action='' method='POST'>";
		
			$out.= "<div class='inwedcal-inputfield'>"; 
				$out.= "<label for='inwedcal_month_number'>Months</label> ";
				$out.= "<select id='inwedcal_month_number' name='inwedcal_month_number' onchange='inwedcal_submitForm();'>";
		
					for($i=1;$i<=12;$i++)
					{
						if($i==$current_month)
						{
							$out.= "<option value='".$i."' selected='selected'>".$i."</option>";
						}
						else
						{
							$out.= "<option value='".$i."'>".$i."</option>";
						}
						
					}
				$out.= "</select>";
			$out.= "</div>";
			
			$out.= "<div class='inwedcal_inputfield'>";
				$out.= "<label for='inwedcal_year_number'>Years</label> ";
		
				$out.= "<select id='inwedcal_year_number' name='inwedcal_year_number' onchange='inwedcal_submitForm();'>";
		
					for($i=($current_year-5);$i<=($current_year+5);$i++)
					{
						if($i==$current_year)
						{
							$out.= "<option value='".$i."' selected='selected'>".$i."</option>";    
						}
						else
						{
							$out.= "<option value='".$i."'>".$i."</option>";
						}
						
					}
		
				$out.= "</select>";
				
				
			$out.= "</div>";
		
		$out.= "</form>";
		
		
		$out.= "<form id='inwedcal_calformnext' class='inwedcal_calforms' action='' method='POST'>";
		
			$out.= "<div class='inwedcal_inputfield'>";
		
				$out.= '<input type="hidden" name="inwedcal_month_number" value='.($current_month+1).'>';
		
				$out.= "<a id='inwedcal_month_number_next'  class='inwedcal_nav_item' href='#' onClick='inwedcal_submitFormNext();' >></a>";
		
			$out.= "</div>";
			
		$out.= "</form>";
			
	$out.= "</div>";
	    $out.= inwedcal_showCurrentMonth($current_month, $current_year);
	return $out;
} 
// register shortcode
add_shortcode('display_calendar', 'inwedcal_display_cal'); 