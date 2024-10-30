<div class="lbc-table lbc-top-adverts-wrapper">
	<div class="lbc-row">
		<?php
		if (count($view_data['adverts']) >= 2) :
			for ($i = 0; $i < 2; $i++) :
		?>
				<div class="lbc-cell lbc-m-col-half <?php echo ($i == 0) ? 'left' : 'right'; ?>">
					<div class="advert">
						<p class="title"><?php echo esc_html($view_data['adverts'][$i]['title']); ?></p>
						<p class="content">
							<?php
							if (isset($view_data['adverts'][$i]['img_link']) && $view_data['adverts'][$i]['img_link'] != '') {
								echo '<a href="' . esc_attr($view_data['adverts'][$i]['img_link']) . '" target="_blank">';
							}
							echo '<img class="cover"' .
									' src="' . esc_attr($view_data['adverts'][$i]['img']) . '"' .
									' alt="' . esc_attr($view_data['adverts'][$i]['img_alt']) . '"' . 
									' title="' . esc_attr($view_data['adverts'][$i][ 'img_' . ((!isset($view_data['adverts'][$i]['img_title']) || $view_data['adverts'][$i]['img_title'] == '') ? 'alt' : 'title') ]) . '"' .
								 '/>';
							if (isset($view_data['adverts'][$i]['img_link']) && $view_data['adverts'][$i]['img_link'] != '') {
								echo '</a>';
							}
							
							echo strip_tags($view_data['adverts'][$i]['content'], '<br><br/><br /><b><i><u><a><div>'); 
							?>
						</p>
					</div>
				</div>
		<?php
			endfor;
		endif;
		?>
	</div>
</div>