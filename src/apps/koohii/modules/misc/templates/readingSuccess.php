<?php
  use_helper('CJK', 'Form', 'Validation');
  $sf_request->setParameter('_homeFooter', true);
?>

<?php slot('inline_styles') ?>
#rtkTooltip     {
  border:1px solid #aaa; background:#fff;
  /*border:4px solid rgba(0,0,0,0.5); -moz-border-radius:4px;-webkit-border-radius:4px;border-radius:4px; */
  -moz-border-radius:4px;-webkit-border-radius:4px;border-radius:4px;
  box-shadow:0 0 3px rgba(0, 0, 0, 0.3); -webkit-box-shadow:0 0 3px rgba(0, 0, 0, 0.3); -moz-box-shadow:0 0 3px rgba(0, 0, 0, 0.3);
}
#rtkTooltip .bd { 
  margin:0; padding:5px 10px;  
  /*-moz-box-radius:4px;-webkit-box-radius:4px;box-radius:4px;*/
  color:#444; font:18px Georgia, Times New Roman, serif;
}
<?php end_slot() ?>

<div id="sight-reading">

    <h2>Kanji Recognition</h2>
    
    <div id="form" style="display:<?php echo $display_form ? 'block':'none' ?>">

      <p>  Copy and paste japanese text into the form below, then click "Show".
        The kanji for which you have flashcards will be <u>hyperlinked</u> to the Study pages, and a popup
        will reveal the Heisig keywords.</p>

      <?php echo form_errors() ?>

      <?php echo form_tag('misc/reading') ?>
        <?php echo textarea_tag('jtextarea', '', ['rows' => 6, 'class' => 'border-box']) ?>
        <?php echo submit_tag('Show', ['class' => 'pure-button']) ?>
      </form>

      <div id="introduction" class="markdown">
        <h3>Purpose of this page</h3>
    
        <p> In <?php echo _CJ('Remembering the Kanji') ?>, the Japanese characters are studied and reviewed
            <i>from the keyword to the kanji</i>.
            In this sight-reading section, you can test your memory the other way round, all the while
            seeing the characters <i>in context</i>.
        </p>
  
        <p> With very basic grammar you can locate compound words made of two or more kanji. You may be
          able to guess the meaning of some words based on the meaning of the characters.
        </p>
        
        <h3>Resources</h3>
        
        <ul>
          <li>Japanese text: <a href="http://www.yomiuri.co.jp" target="_blank">Yomiuri Online</a>,
            <a href="http://www.geocities.co.jp/HeartLand-Gaien/7211/" target="_blank">Old Stories of Japan</a>,
            <a href="http://www.aozora.gr.jp/" target="_blank">Aozora Bunko</a>.
          </li>
          <li><a href="http://www.kanji.org/kanji/japanese/writing/outline.htm" target="_blank">Guide to the Japanese Writing System</a> by Jack Halpern</li>
        </ul>
      </div>
    </div>
    
    <div id="results" style="display:<?php echo $display_kanji ? 'block' : 'none' ?>">
      <p><?php echo link_to('^ Enter more japanese text.',''/*'@sightreading'*/, ['id'=>'toggle-form', 'class' => 'pure-button']) ?></p>
      <p>Point at the colored kanji with the mouse or click/tap to reveal the keyword.
         To study the character, <strong><em>click</em></strong> (or tap) the kanji, 
         and then click the "Study" link inside the tooltip.</p>

      <div class="output"><?php echo cjk_lang_ja($kanji_text) ?></div>
    </div>

</div>

<?php koohii_onload_slot() ?>
(function(){

  var Y = YAHOO,
      Dom = Y.util.Dom,
      Event = Y.util.Event;

  App.Reading =
  {
    formVisible:  false,
    tooltip:      null,

    init: function()
    {
      this.eventDel = new Core.Ui.EventDelegator(document.body, ['click', 'mouseover', 'mouseout']);
      this.eventDel.on('JsTooltip', this.onTooltip, this);
      this.eventDel.onId('toggle-form', this.onToggle, this);
      this.eventDel.onDefault(this.onClick, this);

      //Event.on('toggle-form', 'click', this.onToggle, this, true);
    },

    onToggle: function(ev, el)
    {
      if (ev.type !== 'click')
      {
        return true;
      }

      $$('#introduction').toggle(false);

      this.formVisible = !this.formVisible;
      $$('#form').toggle(this.formVisible);
      
      return false;
    },

    /**
     * Show tooltip on click/touch or mouseover.
     *
     * If the user clicked, disable the tooltip's autohide feature.
     *
     * @return bool    Returns false to stop the event (cf. EventDelegator)
     */
    onTooltip: function(ev, el)
    {
      var data;

      if (this.tooltip && ev.type !== 'mouseout')
      {
        this.tooltip.destroy();
        this.tooltip = null;
      }

      if (ev.type === 'mouseout')
      {
        if (this.tooltip && this.autohide)
        {
          this.tooltip.hide();
        }
      }
      else
      {
        this.autohide = (ev.type !== 'click');

        data = el.dataset;

        html = data.text + ' <a href="' + Dom.getAttribute(el, "href") + '">Study</a>';

        this.tooltip = new App.Ui.CustomTooltip({
          id:       'rtkTooltip',
          context:  el,
          content:  html
        });
        this.tooltip.show();
      }

      return false;
    },

    onClick: function(ev)
    {
      if (ev.type !== 'click')
      {
        return true;
      }

      // clears tooltip if one is on and clicking/tapping in outside
      if (this.tooltip)
      {
        this.tooltip.destroy();
        this.tooltip = null;

        return true;
      }
    }
  };

  App.ready(function(){
    App.Reading.init();
  })

}());
<?php end_slot() ?>

