



<div id="ModalWrapper" class="modal fade"></div>
</div> <!-- </FBODY> -->

<script type="text/javascript">
var ChannelRatings = ChannelRatings ? ChannelRatings : new Object();
ChannelRatings.JSON = ChannelRatings.JSON ? Forms.JSON : new Object();
<?php if (isset($helpjson) == TRUE):?>ChannelRatings.JSON.Help = <?=$helpjson?>;<?php endif;?>
<?php if (isset($alertjson) == TRUE):?>ChannelRatings.JSON.Alerts = <?=$alertjson?>;<?php endif;?>
</script>


