{css}

<div id="div_configuration">
<fieldset>
<legend>Configuration</legend>

<span id="route_configuration_loading" class="loading">loading...</span>

<form id="form_configuration_url" name="form_configuration_url">
    <h4>URL</h4>{outputs>route>base_uri}
    <span class="field">
    <input type="hidden" name="base_uri" id="base_uri" value="{outputs>route>base_uri}"/><br class="clear"/>
    <?php if (array() != {outputs>htmltags>args}): ?>
        <?php foreach ({outputs>htmltags>args} as $tagname => $tag): ?>
        <label>{$tagname}:</label><span class="field">/ {$tag}</span>
        <?php endforeach; ?>
    <?php endif; ?>
    </span>
</form>

<br class="clear"/>

<form id="form_configuration_method" name="form_configuration_method">
    <label>method:</label>
    <span class="field">
    <select id="submit_method" name="submit_method">
    <?php if (array() != {outputs>route>methods}): ?>
        <?php foreach ({outputs>route>methods} as $method): ?>
        <option value="{$method}">{$method}</option>
        <?php endforeach; ?>
    <?php endif; ?>
    </select>
    </span>
</form>

<br class="clear"/>

<?php if (array() != {outputs>htmltags>params}): ?>
    <form id="form_configuration_params" name="form_configuration_params">
        <h4>PARAMS</h4>
        <?php foreach ({outputs>htmltags>params} as $tagname => $tag): ?>
        <label>{$tagname}:</label><span class="field">{$tag}</span>
        <?php endforeach; ?>
    </form>
<?php endif;?>

</fieldset>
<input type="submit" value="view" name="submit_query" id="submit_query"/>
</div>

{js}
{timer}