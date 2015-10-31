<?php
	defined('ABSPATH') or die('Jog on!');

	$ws_ls_tab_index = 1;

	function ws_ls_shortcode()
	{
			// Display error if user not logged in
			if (!is_user_logged_in())	{
				return '<blockquote class="ws-ls-blockquote"><p>' .	__('You need to be logged in to record your weight.', WE_LS_SLUG) . ' <a href="' . wp_login_url(get_permalink()) . '">' . __('Login now', WE_LS_SLUG) . '</a>.</p></blockquote>';
			}

			$html_output = '';

			// Capture and save HTML post?
			if ($_POST && isset($_POST['ws_ls_is_weight_form']) && 'true' == $_POST['ws_ls_is_weight_form']) {
					$save_success = ws_ls_capture_form_validate_and_save();
					if ($save_success) {
						$html_output .= '<blockquote class="ws-ls-blockquote"><p>' . __('Saved!', WE_LS_SLUG) . '</p></blockquote>';
					} else {
						$html_output .= '<blockquote class="ws-ls-blockquote ws-ls-error-text"><p>' . __('An error occurred while saving your data!', WE_LS_SLUG) . '</p></blockquote>';
					}
			}

			// Has the user selected a particular week to look at?
			$selected_week_number = -1;
			if (isset($_POST["week_number"]) && is_numeric($_POST["week_number"])) {
				$selected_week_number = $_POST["week_number"];
			}

			// Load week ranges
			if (ws_ls_is_date_intervals_enabled()) {
				$week_ranges = ws_ls_get_week_ranges();
			}

			// Load user's weight dta (taking into account selected week)
			$weight_data = ws_ls_get_weights(get_current_user_id(), 1000, $selected_week_number);

			// If enabled, render tab header
			if (WE_LS_USE_TABS)	{

				$html_output .= '
						<div id="ws-ls-tabs">
							<ul>
									<li><a>' . __('Overview', WE_LS_SLUG) . '<span>' . __('Chart / Add Weight', WE_LS_SLUG) . '</span></a></li>
									<li><a>' . __('In Detail', WE_LS_SLUG) . '<span>' . __('View all recorded weights', WE_LS_SLUG) . '</span></a></li>
							</ul>
							<div>';
			}

			// Start Chart Tab
			$html_output .= ws_ls_start_tab("wlt-chart");

			if ($weight_data && count($weight_data) > 1) {
				// Great, we have some weight data. Chop it up so we only have (at most) 30 plot points for the graph
				$html_output .= ws_ls_title(__('In a chart', WE_LS_SLUG));
				$weight_data_for_graph = array_slice($weight_data, 0, WE_LS_CHART_MAX_POINTS);
				$html_output .= ws_ls_display_chart($weight_data_for_graph);
			}
			else	{
					$html_output .= '<blockquote class="ws-ls-blockquote"><p>' . __('A pretty graph shall appear once you have recorded several weights.', WE_LS_SLUG) . '</p></blockquote>';
			}

			// Include target form?
			if (WE_LS_ALLOW_TARGET_WEIGHTS) {
				$html_output .= ws_ls_display_weight_form(true, 'ws-ls-target-form');
			}

			// Display input form
			$html_output .= ws_ls_display_weight_form();

			// Close first tab
			$html_output .= ws_ls_end_tab();

			// Start data table tab?
			if (WE_LS_USE_TABS)	{
				$html_output .= ws_ls_start_tab('wlt-weight-history');
			}

			//If we have data, display data table
			if ($weight_data && (count($weight_data) > 0 || $selected_week_number != -1))	{

					if (WE_LS_ALLOW_TARGET_WEIGHTS && WE_LS_USE_TABS) {
						$html_output .= ws_ls_display_weight_form(true, 'ws-ls-target-form');
					}

					// Display week filters and data tab
					$html_output .= ws_ls_title(__('Weight History', WE_LS_SLUG));
					if(count($week_ranges) <= WE_LS_TABLE_MAX_WEEK_FILTERS) {
						$html_output .= ws_ls_display_week_filters($week_ranges, $selected_week_number);
					}
					$html_output .= ws_ls_display_table($weight_data);

			}
			elseif (WE_LS_USE_TABS) {
				$html_output .= __('You haven\'t entered any weight data yet.', WE_LS_SLUG);
			}
			$html_output .= ws_ls_end_tab();
			$html_output .= ws_ls_end_tab();
			$html_output .= ws_ls_end_tab();


			return $html_output;

	}

function ws_ls_start_tab($tab_name)	{
	if (WE_LS_USE_TABS) {
		return '<div>';
	}
	return '';
}
function ws_ls_end_tab()	{
	if (WE_LS_USE_TABS) {
		return '</div>';
	}
	return '';
}
function ws_ls_title($title_text)
{
	return '<h3 class="ws_ls_title">' . $title_text . '</h3>';
}
