<div id="analytics_container">
	<?php if ($is_google_connection_error):?>
		<p class="failure"><?php echo lang('republic_analytics_connection_error');?></p>
	<?php endif;?>

	<?php if( ! empty($google_accounts)) : ?>
		<div class="google_accounts">
			<select name="google_accounts" id="google_accounts">
				<?php foreach ($google_accounts AS $key => $google_account) : ?>
					<?php $is_selected = ( $current_profile == $key ) ? "selected=selected" : ""; ?>
					<option value="<?php echo $key;?>" <?php echo $is_selected;?>><?php echo $google_account['title']; ?></option>
				<?php endforeach;?>
			</select>
		</div>
	<?php else : ?>
		<div class="google_accounts" style="width: 97%;">
			<div style="text-align: center;"><?php print $google_profile_title; ?></div>
		</div>
	<?php endif; ?>

	<?php if ( ! empty($settings['cache'][$current_profile]) && ! empty($settings['cache'][$current_profile]['last_updated'])) :?>
		<p class="analytics_last_updated"><strong><?php echo lang('republic_analytics_last_update');?></strong> <?php echo date('Y-m-d H:i', $settings['cache'][$current_profile]['last_updated']);?>
	<?php endif;?>

<?php
/**********************************************************
 * GRAPH
 **********************************************************/
?>

<div id="analytics_data" class='plot' style="height:300px; margin-top: 50px"></div>


	<?php
	/**********************************************************
	 * PAGE VIEWS
	 **********************************************************/
	?>
	<?php if ($settings['show_pages_view'] == 'y'):?>
	<table class="mainTable pages">
		<thead>
			<tr>
				<th><?php echo lang('republic_analytics_pages');?></th>
				<th class="format_number"><?php echo lang('republic_analytics_visits');?></th>
				<th class="format_percent"><?php echo lang('republic_analytics_bouncerate');?></th>
				<th class="format_time"><?php echo lang('republic_analytics_avg_time_on_page');?></th>
			</tr>
		</thead>
		<?php $i=1;?>
		<?php if (isset($pages)) : ?>
  		<?php foreach($pages AS $key => $page):?>
  			<tr<?php if ($i%2 != 0):?> class="odd" <?php else:?> class="even"<?php endif;?><?php if ($i++ > 10):?> style="display:none"<?php endif;?>>
  				<td><span><a title="<?php echo $page['pageTitle'];?>" href="http://<?php echo $page['hostname'].$page['pagePath'];?>"><?php echo substr($page['pagePath'], 0, 50);?><?php if(strlen($page['pagePath']) > 50):?>...<?php endif;?></a></span></td>
  				<td class="format_number"><?php echo $page['visits'];?></td>
  				<td class="format_percent"><?php if ($page['visits'] > 0):?><?php echo round($page['bounces'] / $page['visits'] * 100, 1);?><?php else:?>0<?php endif;?>&thinsp;%</td>
  				<td class="format_time"><?php echo gmdate("H:i:s",$page['avgTimeOnPage']);?></td>
  			</tr>
  		<?php endforeach;?>
  		<?php if (sizeof($pages) > 10):?>
  			<tr class="more">
  				<td><a href="#" class="showMoreResults">+ <?php echo lang('republic_analytics_more');?></a></td>
  			</tr>
  		<?php endif;?>
  	<?php endif; ?>
	</table>
	<?php endif;?>


	<?php
	/**********************************************************
	 * SOURCES
	 **********************************************************/
	?>
	<?php if ($settings['show_source_view'] == 'y'):?>
	<table class="mainTable sources">
		<thead>
			<tr>
				<th><?php echo lang('republic_analytics_sources');?></th>
				<th class="format_number"><?php echo lang('republic_analytics_visits');?></th>
				<th class="format_percent"><?php echo lang('republic_analytics_bouncerate');?></th>
				<th class="format_time"><?php echo lang('republic_analytics_avg_time_on_site');?></th>
			</tr>
		</thead>
		<?php $i=1;?>
		<?php foreach($sources AS $key => $source):?>
			<tr<?php if ($i%2 != 0):?> class="odd" <?php else:?> class="even"<?php endif;?><?php if ($i++ > 10):?> style="display:none"<?php endif;?>>
				<td><?php if (preg_match('/[a-zA-Z]+\.[a-zA-Z]+/', $key)): ?><a href="http://<?php echo $key;?>"><?php endif; ?><?php echo $key;?></a></td>
				<td class="format_number"><?php echo $source['visits'];?></td>
				<td class="format_percent"><?php if ($source['visits'] > 0):?><?php echo round($source['bounces']/$source['visits'] * 100, 1);?><?php else: ?>0<?php endif;?>&thinsp;%</td>
				<td class="format_time"><?php echo gmdate("H:i:s",$source['avgTimeOnSite']);?></td>
			</tr>
		<?php endforeach;?>
		<?php if (sizeof($sources) > 10):?>
			<tr class="more">
				<td><a href="#" class="showMoreResults">+ <?php echo lang('republic_analytics_more');?></a></td>
			</tr>
		<?php endif;?>
	</table>
	<?php endif;?>

	<div style="clear:both"></div>

	<?php
	/**********************************************************
	 * TODAY
	 **********************************************************/
	?>
	<?php if ($settings['show_today_view'] == 'y'):?>
	<table class="mainTable today">
		<thead>
			<tr>
				<th colspan="3"><?php echo lang('republic_analytics_today');?></th>
			</tr>
		</thead>

		<?php if (empty($today)):?>
			<tbody>
				<tr class="odd">
					<td colspan="3"><?php echo lang('republic_analytics_today_error');?></td>
				</tr>
			</tbody>

		<?php else:?>
			<tbody>
				<tr class="odd">
					<td class="format_visits"><?php echo lang('republic_analytics_visits');?></td>
					<td class="format_visits format_number"><?php echo $today['visits'];?></td>
					<td class="format_visits format_percent">100&thinsp;%</td>
				</tr>
				<tr class="even">
					<td class="format_visits"><?php echo lang('republic_analytics_visitors');?></td>
					<td class="format_visits format_number"><?php echo $today['visitors'];?></td>
					<td class="format_visits format_percent">-</td>
				</tr>
				<tr class="odd">
					<td class="format_visits"><?php echo lang('republic_analytics_page_views');?></td>
					<td class="format_visits format_number"><?php echo $today['pageViews'];?></td>
					<td class="format_visits format_percent">-</td>
				</tr>
				<tr class="even">
					<td><?php echo lang('republic_analytics_new_visits');?></td>
					<td class="format_number"><?php echo $today['newVisits'];?></td>
					<td class="format_percent"><?php if ($today['visits'] > 0):?><?php echo round(($today['newVisits']/$today['visits']*100),0);?><?php else: ?>0<?php endif;?>&thinsp;%</td>
				</tr>
				<tr class="odd">
					<td colspan="2"><?php echo lang('republic_analytics_avg_time_on_site');?></td>
					<td class="format_time"><?php echo gmdate("H:i:s",$today['avgTimeOnSite']);?></td>
				</tr>
			</tbody>
			<?php if ($settings['show_browser_view'] == 'y'):?>
				<thead>
					<tr>
						<th class="republic_analytics_browser" colspan="3"><?php echo lang('republic_analytics_browser');?></th>
					</tr>
				</thead>
				<tbody class="expandable">
					<?php if (empty($today['browser'])) : ?>
						<tr>
							<td colspan="3"><?php echo lang('republic_analytics_no_data'); ?></td>
						</tr>
					<?php endif; ?>
					<? $i = 0;?>
					<?php foreach($today['browser'] AS $browser => $value):?>
						<tr class="<?php if ($i % 2 == 0):?>odd<?php else:?>even<?php endif;?>">
							<td><a href="#" class"clickable" id="today_browser_<?php echo $i;?>"><?php echo $browser;?></a></td>
							<td class="format_number"><?php echo $value['total']?></td>
							<td class="format_percent"><?php echo round($value['total']/$today['visits'] * 100, 1);?>&thinsp;% </td>
						</tr>
						<?php foreach($value['version'] AS $version => $version_count):?>
							<tr class="browser-sub today_browser_<?php echo $i;?>">
								<td class="version"><?php echo $version;?></td>
								<td class="format_number"><?php echo $version_count?></td>
								<td class="format_percent"><?php if ($today['visits'] > 0):?><?php echo round($version_count / $today['visits'] * 100, 1);?><?php else: ?>0<?php endif;?>&thinsp;%</td>
							</tr>
						<?php endforeach;?>
						<?php $i++;?>
					<?php endforeach;?>
				</tbody>
			<?php endif;?>
			<?php if ($settings['show_operativsystem_view'] == 'y'):?>
			<thead>
				<tr>
					<th class="republic_analytics_os" colspan="3"><?php echo lang('republic_analytics_os');?></th>
				</tr>
			</thead>
			<tbody class="expandable">
				<?php if (empty($today['operativsystems'])) : ?>
					<tr>
						<td colspan="3"><?php echo lang('republic_analytics_no_data'); ?></td>
					</tr>
				<?php endif; ?>
				<? $i = 0;?>
				<?php foreach($today['operativsystems'] AS $system => $value):?>
					<tr class="<?php if ($i % 2 == 0):?>odd<?php else:?>even<?php endif;?>">
						<td><?php echo $system;?></td>
						<td class="format_number"><?php echo $value;?></td>
						<td class="format_percent"><?php if ($today['visits'] > 0):?><?php echo round($value/$today['visits'] * 100, 1);?><?php else: ?>0<?php endif;?>&thinsp;% </td>
					</tr>
					<?php $i++;?>
				<?php endforeach;?>
			</tbody>
			<?php endif;?>

		<?php endif;?>
	</table>
	<?php endif;?>


	<?php
	/**********************************************************
	 * YESTERDAY
	 **********************************************************/
	?>
	<?php if ($settings['show_yesterday_view'] == 'y'):?>
	<table class="mainTable yesterday">
		<thead>
			<tr>
				<th colspan="3"><?php echo lang('republic_analytics_yesterday');?></th>
			</tr>
		</thead>

		<?php if (empty($yesterday)):?>
			<tbody>
				<tr class="odd">
					<td colspan="3"><?php echo lang('republic_analytics_yesterday_error');?></td>
				</tr>
			</tbody>

		<?php else:?>
			<tbody>
				<tr class="odd">
					<td class="format_visits"><?php echo lang('republic_analytics_visits');?></td>
					<td class="format_visits format_number"><?php echo $yesterday['visits'];?></td>
					<td class="format_visits format_percent">100&thinsp;%</td>
				</tr>
				<tr class="even">
					<td class="format_visits"><?php echo lang('republic_analytics_visitors');?></td>
					<td class="format_visits format_number"><?php echo $yesterday['visitors'];?></td>
					<td class="format_visits format_percent">-</td>
				</tr>
				<tr class="odd">
					<td class="format_visits"><?php echo lang('republic_analytics_page_views');?></td>
					<td class="format_visits format_number"><?php echo $yesterday['pageViews'];?></td>
					<td class="format_visits format_percent">-</td>
				</tr>
				<tr class="even">
					<td><?php echo lang('republic_analytics_new_visits');?></td>
					<td class="format_number"><?php echo $yesterday['newVisits'];?></td>
					<td class="format_percent"><?php if ($yesterday['visits'] > 0):?><?php echo round(($yesterday['newVisits'] / $yesterday['visits']*100),0);?><?php else : ?>0<?php endif;?>&thinsp;%</td>
				</tr>
				<tr class="odd">
					<td colspan="2"><?php echo lang('republic_analytics_avg_time_on_site');?></td>
					<td class="format_time"><?php echo gmdate("H:i:s",$yesterday['avgTimeOnSite']);?></td>
				</tr>
			</tbody>
			<?php if ($settings['show_browser_view'] == 'y'):?>
				<thead>
					<tr>
						<th class="republic_analytics_browser" colspan="3"><?php echo lang('republic_analytics_browser');?></th>
					</tr>
				</thead>
				<tbody class="expandable">
					<?php if (empty($yesterday['browser'])) : ?>
						<tr>
							<td colspan="3"><?php echo lang('republic_analytics_no_data'); ?></td>
						</tr>
					<?php endif; ?>
					<? $i = 0;?>
					<?php foreach($yesterday['browser'] AS $browser => $value):?>
						<tr class="<?php if ($i % 2 == 0):?>odd<?php else:?>even<?php endif;?>">
							<td><a href="#" class"clickable" id="yesterday_browser_<?php echo $i;?>"><?php echo $browser;?></a></td>
							<td class="format_number"><?php echo $value['total']?></td>
							<td class="format_percent"><?php if ($yesterday['visits'] > 0):?><?php echo round($value['total'] / $yesterday['visits'] * 100, 1);?><?php else : ?>0<?php endif;?>&thinsp;% </td>
						</tr>
						<?php foreach($value['version'] AS $version => $version_count):?>
							<tr class="browser-sub yesterday_browser_<?php echo $i;?>">
								<td class="version"><?php echo $version;?></td>
								<td class="format_number"><?php echo $version_count?></td>
								<td class="format_percent"><?php if ($yesterday['visits'] > 0):?><?php echo round($version_count / $yesterday['visits'] * 100, 1);?><?php else : ?>0<?php endif;?>&thinsp;%</td>
							</tr>
						<?php endforeach;?>
						<?php $i++;?>
					<?php endforeach;?>
				</tbody>
			<?php endif;?>
			<?php if ($settings['show_operativsystem_view'] == 'y'):?>
			<thead>
				<tr>
					<th class="republic_analytics_os" colspan="3"><?php echo lang('republic_analytics_os');?></th>
				</tr>
			</thead>
			<tbody class="expandable">
				<?php if (empty($yesterday['operativsystems'])) : ?>
					<tr>
						<td colspan="3"><?php echo lang('republic_analytics_no_data'); ?></td>
					</tr>
				<?php endif; ?>
				<? $i = 0;?>
				<?php foreach($yesterday['operativsystems'] AS $system => $value):?>
					<tr class="<?php if ($i % 2 == 0):?>odd<?php else:?>even<?php endif;?>">
						<td><?php echo $system;?></td>
						<td class="format_number"><?php echo $value;?></td>
						<td class="format_percent"><?php if ($yesterday['visits'] > 0):?><?php echo round($value / $yesterday['visits'] * 100, 1);?><?php else : ?>0<?php endif;?>&thinsp;% </td>
					</tr>
					<?php $i++;?>
				<?php endforeach;?>
			</tbody>
			<?php endif;?>

		<?php endif;?>
	</table>
	<?php endif;?>


	<?php
	/**********************************************************
	 * WEEK
	 **********************************************************/
	?>
	<?php if ($settings['show_week_view'] == 'y'):?>
	<table class="mainTable week">
		<thead>
			<tr>
				<th colspan="3"><?php echo lang('republic_analytics_week');?></th>
			</tr>
		</thead>
			<tbody>
				<tr class="odd">
					<td class="format_visits"><?php echo lang('republic_analytics_visits');?></td>
					<td class="format_visits format_number"><?php echo $week['visits'];?></td>
					<td class="format_visits format_percent">100&thinsp;%</td>
				</tr>
				<tr class="even">
					<td class="format_visits"><?php echo lang('republic_analytics_visitors');?></td>
					<td class="format_visits format_number"><?php echo $week['visitors'];?></td>
					<td class="format_visits format_percent">-</td>
				</tr>
				<tr class="odd">
					<td class="format_visits"><?php echo lang('republic_analytics_page_views');?></td>
					<td class="format_visits format_number"><?php echo $week['pageViews'];?></td>
					<td class="format_visits format_percent">-</td>
				</tr>
				<tr class="even">
					<td><?php echo lang('republic_analytics_new_visits');?></td>
					<td class="format_number"><?php echo $week['newVisits'];?></td>
					<td class="format_percent"><?php if ($week['visits'] > 0):?><?php echo round(($week['newVisits']/$week['visits']*100),0);?><?php endif;?>&thinsp;%</td>
				</tr>
				<tr class="odd">
					<td colspan="2"><?php echo lang('republic_analytics_avg_time_on_site');?></td>
					<td class="format_time"><?php echo gmdate("H:i:s",$week['avgTimeOnSite']);?></td>
				</tr>
			</tbody>
			<?php if ($settings['show_browser_view'] == 'y'):?>
				<thead>
					<tr>
						<th class="republic_analytics_browser" colspan="3"><?php echo lang('republic_analytics_browser');?></th>
					</tr>
				</thead>
				<tbody class="expandable">
					<?php if (empty($week['browser'])) : ?>
						<tr>
							<td colspan="3"><?php echo lang('republic_analytics_no_data'); ?></td>
						</tr>
					<?php endif; ?>
					<? $i = 0;?>
					<?php foreach($week['browser'] AS $browser => $value):?>
						<tr class="<?php if ($i % 2 == 0):?>odd<?php else:?>even<?php endif;?>">
							<td><a href="#" class"clickable" id="week_browser_<?php echo $i;?>"><?php echo $browser;?></a></td>
							<td class="format_number"><?php echo $value['total']?></td>
							<td class="format_percent"><?php if ($week['visits'] > 0):?><?php echo round($value['total']/$week['visits'] * 100, 1);?><?php else : ?>0<?php endif;?>&thinsp;% </td>
						</tr>
						<?php foreach($value['version'] AS $version => $version_count):?>
							<tr class="browser-sub week_browser_<?php echo $i;?>">
								<td class="version"><?php echo $version;?></td>
								<td class="format_number"><?php echo $version_count?></td>
								<td class="format_percent"><?php if ($week['visits'] > 0):?><?php echo round($version_count/$week['visits'] * 100, 1);?><?php else : ?>0<?php endif;?>&thinsp;%</td>
							</tr>
						<?php endforeach;?>
						<?php $i++;?>
					<?php endforeach;?>
				</tbody>
			<?php endif;?>
			<?php if ($settings['show_operativsystem_view'] == 'y'):?>
			<thead>
				<tr>
					<th class="republic_analytics_os" colspan="3"><?php echo lang('republic_analytics_os');?></th>
				</tr>
			</thead>
			<tbody class="expandable">
				<?php if (empty($week['operativsystems'])) : ?>
					<tr>
						<td colspan="3"><?php echo lang('republic_analytics_no_data'); ?></td>
					</tr>
				<?php endif; ?>
				<? $i = 0;?>
				<?php foreach($week['operativsystems'] AS $system => $value):?>
					<tr class="<?php if ($i % 2 == 0):?>odd<?php else:?>even<?php endif;?>">
						<td><?php echo $system;?></td>
						<td class="format_number"><?php echo $value;?></td>
						<td class="format_percent"><?php if ($week['visits'] > 0):?><?php echo round($value/$week['visits'] * 100, 1);?><?php else : ?>0<?php endif;?>&thinsp;% </td>
					</tr>
					<?php $i++;?>
				<?php endforeach;?>
			</tbody>
		<?php endif;?>
	</table>
	<?php endif;?>


	<?php
	/**********************************************************
	 * MONTH
	 **********************************************************/
	?>
	<?php if ($settings['show_month_view'] == 'y'):?>
	<table class="mainTable month">
		<thead>
			<tr>
				<th colspan="3"><?php echo lang('republic_analytics_month');?></th>
			</tr>
		</thead>
			<tbody>
				<tr class="odd">
					<td class="format_visits"><?php echo lang('republic_analytics_visits');?></td>
					<td class="format_visits format_number"><?php echo $month['visits'];?></td>
					<td class="format_visits format_percent">100&thinsp;%</td>
				</tr>
				<tr class="even">
					<td class="format_visits"><?php echo lang('republic_analytics_visitors');?></td>
					<td class="format_visits format_number"><?php echo $month['visitors'];?></td>
					<td class="format_visits format_percent">-</td>
				</tr>
				<tr class="odd">
					<td class="format_visits"><?php echo lang('republic_analytics_page_views');?></td>
					<td class="format_visits format_number"><?php echo $month['pageViews'];?></td>
					<td class="format_visits format_percent">-</td>
				</tr>
				<tr class="even">
					<td><?php echo lang('republic_analytics_new_visits');?></td>
					<td class="format_number"><?php echo $month['newVisits'];?></td>
					<td class="format_percent"><?php if ($month['visits'] > 0):?><?php echo round(($month['newVisits']/$month['visits']*100),0);?><?php endif;?>&thinsp;%</td>
				</tr>
				<tr class="odd">
					<td colspan="2"><?php echo lang('republic_analytics_avg_time_on_site');?></td>
					<td class="format_time"><?php echo gmdate("H:i:s",$month['avgTimeOnSite']);?></td>
				</tr>
			</tbody>
			<?php if ($settings['show_browser_view'] == 'y'):?>
				<thead>
					<tr>
						<th class="republic_analytics_browser" colspan="3"><?php echo lang('republic_analytics_browser');?></th>
					</tr>
				</thead>
				<tbody class="expandable">
					<?php if (empty($month['browser'])) : ?>
						<tr>
							<td colspan="3"><?php echo lang('republic_analytics_no_data'); ?></td>
						</tr>
					<?php endif; ?>
					<? $i = 0;?>
					<?php foreach($month['browser'] AS $browser => $value):?>
						<tr class="<?php if ($i % 2 == 0):?>odd<?php else:?>even<?php endif;?>">
							<td><a href="#" class"clickable" id="month_browser_<?php echo $i;?>"><?php echo $browser;?></a></td>
							<td class="format_number"><?php echo $value['total']?></td>
							<td class="format_percent"><?php if ($month['visits'] > 0):?><?php echo round($value['total']/$month['visits'] * 100, 1);?><?php else : ?>0<?php endif;?>&thinsp;% </td>
						</tr>
						<?php foreach($value['version'] AS $version => $version_count):?>
							<tr class="browser-sub month_browser_<?php echo $i;?>">
								<td class="version"><?php echo $version;?></td>
								<td class="format_number"><?php echo $version_count?></td>
								<td class="format_percent"><?php if ($month['visits'] > 0):?><?php echo round($version_count/$month['visits'] * 100, 1);?><?php else : ?>0<?php endif;?>&thinsp;%</td>
							</tr>
						<?php endforeach;?>
						<?php $i++;?>
					<?php endforeach;?>
				</tbody>
			<?php endif;?>
			<?php if ($settings['show_operativsystem_view'] == 'y'):?>
			<thead>
				<tr>
					<th class="republic_analytics_os" colspan="3"><?php echo lang('republic_analytics_os');?></th>
				</tr>
			</thead>
			<tbody class="expandable">
				<?php if (empty($month['operativsystems'])) : ?>
					<tr>
						<td colspan="3"><?php echo lang('republic_analytics_no_data'); ?></td>
					</tr>
				<?php endif; ?>
				<? $i = 0;?>
				<?php foreach($month['operativsystems'] AS $system => $value):?>
					<tr class="<?php if ($i % 2 == 0):?>odd<?php else:?>even<?php endif;?>">
						<td><?php echo $system;?></td>
						<td class="format_number"><?php echo $value;?></td>
						<td class="format_percent"><?php if ($month['visits'] > 0):?><?php echo round($value/$month['visits'] * 100, 1);?><?php else : ?>0<?php endif;?>&thinsp;% </td>
					</tr>
					<?php $i++;?>
				<?php endforeach;?>
			</tbody>
		<?php endif;?>
	</table>
	<?php endif;?>

	<?php
	/**********************************************************
	* DATA
	**********************************************************/
	?>
	<div id="data_for_graph" style="display:none">
		<div class="graphTitle"><?php echo $google_profile_title;?></div>
		<div class="dateFirst"><?php echo $table_data['dateFirst']; ?></div>
		<div class="dateLast"><?php echo $table_data['dateLast']; ?></div>

		<div class="lineGoogleVisits"><?php echo $table_data['countGoogleVisits']; ?></div>
		<div class="lineGoogleVisitors"><?php echo $table_data['countGoogleVisitors']; ?></div>
		<div class="lineGooglePageViews"><?php echo $table_data['countGooglePageViews']; ?></div>

		<div class="tickersGoogleVisits"><?php echo $table_data['dateGoogleVisits']; ?>]</div>
		<div class="tickeIntervalGoogleVisits"><?php echo $table_data['maxGoogleVisitsY']; ?></div>
		<div class="tickeIntervalGoogleVisitors"><?php echo $table_data['maxGoogleVisitorsY']; ?></div>
		<div class="tickeIntervalGooglePageViews"><?php echo $table_data['maxGooglePageViewsY']; ?></div>

		<div class="labelVisits"><?php echo lang('republic_analytics_visits');?></div>
		<div class="labelVisitors"><?php echo lang('republic_analytics_visitors');?></div>
		<div class="labelPageViews"><?php echo lang('republic_analytics_page_views');?></div>
		<div class="graphType"><?php echo $settings['graph_type'];?></div>
		<div class="graphVisitsColor"><?php echo $settings['visits_color'];?></div>
		<div class="graphVisitorsColor"><?php echo $settings['visitors_color'];?></div>
		<div class="graphPageViewsColor"><?php echo $settings['pages_view_color'];?></div>

		<div class="showPagesViewInTable"><?php echo $settings['show_pages_view_in_table'];?></div>
	</div>
</div>
