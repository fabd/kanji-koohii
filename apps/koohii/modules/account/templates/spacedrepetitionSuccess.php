<?php
  use_helper('Form', 'Validation', 'Decorator');

?>
<?php slot('inline_styles') ?>
/* ... */
.srs_intervals { margin:0.5em 0 1em; }
.srs_intervals em { display:inline-block; margin:0 1em 0 0; }
.srs_intervals-box {
  display:inline-block; margin:0 0.5em 0 0; padding:0.2em 0.5em 0.1em; border-radius:5px;
  font-family:monospace;
  background:#d7e0b5; color:#485f27; border-bottom:1px solid #aab38a;
}
<?php end_slot() ?>

<?php decorate_start('SideTabs', array('active' => 'spacedrepetition')) ?>

  <h2>Spaced Repetition Settings</h2>

  <p>
    Here you can configure the Spaced Repetition settings.
    See <?= link_to('documentation', '@learnmore#help-srs') ?> for Kanji Koohii's implementation of the Leitner SRS.
  </p>

  <?php echo form_errors() ?>
  <?php echo form_tag('account/spacedrepetition') ?>

  <div id="vue-form">
    <srs-form-component></srs-form-component>
  </div>

    <p>
      <?php echo submit_tag('Save changes', array('class' => 'btn btn-success')) ?>
    </p>

  </form>

<?php decorate_end() ?>

<script type="text/x-template" id="settings-srs-template">
  <div><!-- I'm just a root -->

      <div class="form-group mb-2">
        
        <label for="srs_max_box">Number of boxes</label>
        <span class="help-block">
How many boxes in total, <em>excluding</em> the leftmost box which contains New and Failed cards.
        </span>

        <select name="opt_srs_max_box" v-model="srs_max_box" class="form-control" style="max-width: 10em;" id="srs_max_box">
          <option v-for="o in srs_max_box_values" :value="o[0]">{{ o[1] }}</option>
        </select>

      </div>

      <div class="form-group mb-2">

        <label for="srs_mult">Review interval multiplier</label>
        <span class="help-block">
The multiplier determines the spacing between each successive review.
The first interval is always 3 days.
        </span>
        <div class="srs_intervals">
          <em>Intervals (days):</em><span v-for="i in intervals" class="srs_intervals-box">{{ i.days }}</span>
        </div>
        <select name="opt_srs_mult" v-model="srs_mult" class="form-control" style="max-width: 10em;" id="srs_mult">
          <option v-for="o in srs_mult_values" :value="o[0]">{{ o[1] || o[0] }}</option>
        </select>

      </div>

      <div class="form-group mb-2" :class="{'has-error': !isValidHardBox}">

        <label for="srs_hard_box">Maximum box for cards marked 'Hard'</label>
        <span class="help-block">
Here, you can chose the maximum interval for a Hard answer by limiting the upper box. So for example if you
chose to use 10 boxes and a Hard answer limit of 5 then a card in box 6,7,8,9 and 10 will always drop down to 5.
        </span>

        <div class="srs_intervals">
          <em>Max interval for Hard answer:</em>
          <span>
            {{ srs_hard_box > 0 ?
                nthInterval(srs_hard_box) + ' days' :
                '(default : drop card by one box, use the lower box interval)' }}
          </span>
        </div>

        <select name="opt_srs_hard_box" v-model="srs_hard_box" class="form-control" style="max-width: 10em;" id="srs_hard_box">
          <option v-for="o in srs_hard_box_values" :value="o[0]">{{ o[1] || o[0] }}</option>
        </select>

        <span class="has-error-msg" v-if="!isValidHardBox">
          <strong>Max Hard Box must be lower than the number of boxes total.</strong>
        </span>

      </div>

  </div>
</script>

<?php koohii_onload_slot() ?>
App.ready(function(){

  Koohii.SRS = { settings: <?= json_encode($srs_settings) ?> };

  // NOTE : the validation needs to be kept in sync with the backend (account/spacedrepetition)

  Vue.component('srs-form-component', {
    
    template: '#settings-srs-template',
  
    data() {
      return {
        // form
        srs_max_box:  0,
        srs_mult:     0,    // integer (205 means 2.05)
        srs_hard_box: 0,

        // select options
        srs_max_box_values:  [ [5, "5"], [6, "6"], [7,"7 (default)"], [8, "8"], [9, "9"], [10, "10"] ],
        srs_hard_box_values: [ [0, '(default)'], [1], [2], [3], [4], [5], [6], [7], [8], [9] ]
      }
    },

    methods: {

      nthInterval(n) {
        let first  = 3;
        let mult   = 1.0 * Number(this.srs_mult / 100).toFixed(2); // 205 => 2.05
        return  Math.ceil( first * Math.pow(mult, n - 1) );
      }

    },

    computed: {

      isValidHardBox() {
        return this.srs_hard_box < this.srs_max_box;
      },

      srs_mult_values() {
        let m       = 130;
        let options = [];
        while (m <= 400) {
          let label = m === 205 ? '2.05 (default)' : Number(m / 100).toFixed(2);
          options.push( [m, label] );
          m += 5;
        }
        return options;
      },

      intervals() {

        let values = [];

        for (let n = 1; n <= this.srs_max_box; n++) {
          let days = this.nthInterval(n);
          values.push( { days: days } );
        }

        return values;
      }

    },

    created() {
      //Core.log('created() %o', this.srs_max_box);

      this.srs_max_box  = Koohii.SRS.settings[0];
      this.srs_mult     = Koohii.SRS.settings[1];
      this.srs_hard_box = Koohii.SRS.settings[2];
    }
  })

  new Vue({
    el: '#vue-form'
  })

});
<?php end_slot() ?>
