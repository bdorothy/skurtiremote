<div class="modal-header">
      <a class="close" data-dismiss="modal">&times;</a>
      <h3><?=lang('cr:alert:edit_rating')?></h3>
</div>

<div class="modal-body">
      <table class="FormTable" cellspacing="0" cellpadding="0" border="0" width="100%">
      <tbody>
            <tr>
                  <td>
                        <?php if($rating->rating_author_id > 0):?>
                        <strong><?=lang('cr:rating_author')?>:</strong> <?=$member->screen_name?> <br />
                        <strong><?=lang('cr:email')?>:</strong> <?=$member->email?> <br />
                        <?php else:?>
                        <strong><?=lang('cr:rating_author')?>:</strong> <?=lang('cr:guest')?> <br />
                        <?php endif;?>
                        <strong><?=lang('cr:ip')?>:</strong> <?=long2ip($rating->ip_address)?>
                  </td>
                  <td>
                        <strong><?=lang('cr:rating_type')?>:</strong> <?=lang('cr:type_long:'.$rating_type)?> <br />
                        <strong><?=lang('cr:rating_date')?>:</strong> <?=$this->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $rating->rating_date)?> <br />
                  </td>
            </tr>
      </tbody>
      </table>

      <table class="FormTable" cellspacing="0" cellpadding="0" border="0" width="100%">
      <tbody>
            <tr>
                  <td><label><?=lang('cr:status')?></label></td>
                  <?php $options = array('0'=>lang('cr:closed'), '1'=>lang('cr:open'));?>
                  <td><?=form_dropdown('rating_status', $options, $rating->rating_status);?></td>
            </tr>

            <?php foreach($fields as $field):?>
            <tr>
                  <td><label><?=$field->title?></label></td>
                  <td><input name="ratingfield[<?=$field->field_id?>]" type="text" value="<?=$field->rating?>"></td>
            </tr>
            <?php endforeach;?>
      </tbody>
      </table>

      <input name="rating_id" type="hidden" value="<?=$rating_id?>">
</div>



<div class="modal-footer">
      <a href="#" class="btn btn-primary"><?=lang('cr:save')?> <?=lang('cr:rating')?></a>
      <a href="#" class="btn" data-dismiss="modal"><?=lang('cr:close')?></a>
</div>