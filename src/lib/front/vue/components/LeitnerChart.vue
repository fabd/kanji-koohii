<template>

  <transition name="chart-fade" appear>

  <div v-once class="leitner-chart_outer">

    <div v-for="(bar, b) in displayBoxes" :key="b" class="box">
      <div class="box_inner">
        <div :class="{ lbl: true, first: b===0 }" v-html="getBoxLabel(b)"></div>
        <a href="#" class="bar bar1" :style="{
          height: getHeight(bar[0]),
          backgroundColor: getColor(bar[0], 0)
        }" @click="onClick($event, bar[0])">
          <div class="side" :style="{backgroundColor: getColor(bar[0], 1)}"></div>
          <div class="top" :style="{backgroundColor: getColor(bar[0], 2)}"></div>
          <span :class="[ 'val', bar[0].value ? '' : 'val-zero' ]">{{ bar[0].value }}</span>
        </a>
        <a href="#" class="bar bar2" :style="{
          height: getHeight(bar[1]),
          backgroundColor: getColor(bar[1], 0)
        }" @click="onClick($event, bar[1])">
          <div class="side" :style="{backgroundColor: getColor(bar[1], 1)}"></div>
          <div class="top" :style="{backgroundColor: getColor(bar[1], 2)}"></div>
          <span :class="[ 'val', bar[1].value ? '' : 'val-zero' ]">{{ bar[1].value }}</span>
        </a>
      </div>
    </div>

  </div>

  </transition>

</template>

<script>
export default {

  props: {
    // id of a parent container used to determine the available horizontal space
    'containerId': { type: String, required: true }
  },

  data: function() {
    return {

      /* global leitner_chart_data */
      box_data: leitner_chart_data.boxes,
      box_urls: leitner_chart_data.urls,
      
      colors: {
        // bar.type
        failed: ['#ff8257','#d2633f','#ffa994'],
        new:    ['#40a8e5','#3d83ac','#8abde4'],
        fresh:  ['#40e569','#3dac58','#8ae49c'],
        due:    ['#ffae57','#d2633f','#ffcc7f'],
        nill:   ['#929292','#818181','#b1b1b1']
      }

      // itsMounted: false
    }
  },

  computed: {
    // less than ideal solution due to device orientation switch
    numDisplayBoxes() {
      let numSrsBoxes = this.box_data.length;

      let el, containerWidth = (el = document.getElementById(this.containerId)) && el.offsetWidth || 0
     
      // console.log('container width: %d     num boxes: %d', containerWidth, numSrsBoxes)

      let isWide = (containerWidth >= 500)

      let maxVisibleBoxes = isWide ? 11 : 8

      return Math.min(maxVisibleBoxes, numSrsBoxes)
    },

    // was intended to display 5 boxes in portrait, 8 in landscape
    displayBoxes() {
      // console.log("displayBoxes()")
      let maxBox = this.numDisplayBoxes, boxes = []

      this.box_data.map( (b, i) => {
        if (i < maxBox) {
          boxes.push(b)
        }
        else {
          boxes[maxBox-1][0].value += b[0].value
          boxes[maxBox-1][1].value += b[1].value
        }
      })

      while (boxes.length < maxBox) {
        boxes.push([{"value":0},{"value":0}])
      }

console.log(boxes);

      return boxes
    },

    // flatten boxes (two stacks each) into an array of stacks
    stacks: function() {
      // console.log("get property: stacks ...")
      let bars = []
      this.displayBoxes.map( b => { bars.push(b[0], b[1]) })
      return bars
    },

    maxHeight: function() {
      // console.log("get property: maxHeight ...")
      var vals = this.stacks.map(function(s) { return s.value; })
      var c = Math.max.apply(null, vals)
      return c
    }
  },

  beforeMount() {
    this.prepareStacks()
  },

  methods: {
    getBoxLabel(box) {
      const lastBox = this.numDisplayBoxes - 1
      return box === 0 ? 'Fail &<br>New' : (box < lastBox ? box : box + '+')
    },

    getColor(bar, face) {
      return this.colors[bar.type][face]
    },

    getPercent(height) {
      return this.maxHeight > 0 ? Math.ceil(height * 100 / this.maxHeight) : 0;
    },

    getHeight(bar) {
      var height = this.getPercent(bar.value);

      return (height >= 0 && height < 4 ? '4px' : height + '%');
    },

    onClick(event, bar) {
      let url = this.getBarUrl(bar)

      // console.log('bar %d  type %s   go to %s' , bar.index, bar.type, url)

      if (url !== '') {
        window.location = url
        return true
      }

      event.preventDefault()
      return false
    },

    // return a destination url for restudy / new / due piles
    getBarUrl(bar) {
      let url = ''
      if (bar.value > 0) {
        if (bar.index < 2) {
          url = this.box_urls[ bar.index === 0 ? 'restudy' : 'new' ]
        }
        else if (bar.type === 'due') {
          url = this.box_urls['due'] + '&box=' + ((bar.index >> 1) + 1)
        }
      }
      return url
    },

    // bar type is fixed in the SRS chart design, "nill" is dynamic and for empty bars
    getBarType(bar) {
      let i = bar.index, type

      if (bar.value <= 0) {
        type = 'nill'
      } else if (i < 2) {
        type = i & 1 ? 'new' : 'failed'
      } else {
        type = i & 1 ? 'fresh' : 'due'
      }

      return type
    },

    // set index & type for all the bars in the chart
    prepareStacks() {
      this.displayBoxes.forEach( (bars, index) => {
        bars[0].index = index * 2
        bars[0].type  = this.getBarType(bars[0])

        bars[1].index = index * 2 + 1
        bars[1].type  = this.getBarType(bars[1])
      })
    },

  }

  /*
  mounted: function () {
    this.$nextTick(function () {
      // Code that will run only after the entire view (+children) has been rendered
      console.log('mounted yo')
      this.itsMounted = true
    })
  }*/
}
</script>

<style>

  /* this is the parent container of this vue component... FIXME should move here eventually */
#leitner-chart_pane { padding:1em; /* to avoid content jumping while loading */min-height:298px; }

  /* fade in the bar chart, along with the min-height on container, looks nicer when page load is slow */
.chart-fade-enter { opacity:0; }
.chart-fade-enter-active { transition:opacity 0.3s; }

.leitner-chart_outer { display:table; width:100%; padding:30px 0 40px; }
.leitner-chart_outer .box  { display:table-cell; }

.box_inner {
  position:relative;
  height:200px;
}

  /* the link */
.box .bar { outline:none; }
.box .bar:hover .val { font-weight:bold; }

  /* 3D effect */
.box .bar .side {
  position: absolute;
  width: 7px; height: 100%; left: 100%;
  background-color: #777;
  transform: skew(0, -45deg);
  transform-origin: top left;
}
.box .bar .top {
  position: absolute;
  width: 100%; height: 7px; bottom: 100%;
  background-color: #AAA;
  transform: skew(-45deg);
  transform-origin: bottom right;
}

  /* two stacks per box */
.box .bar1 { position:absolute; left:11%; bottom:0;   width:39%;   background:#f8f8f8; }
.box .bar2 { position:absolute; left:50%; bottom:0;   width:39%;   background:#f8c8f8; }

.box .lbl {
  position:absolute; left:0; right:0; bottom:-37px; /*width:100%; height:24px; */
  margin-bottom:4px; 
  color: #a9a396; font-size:17px; line-height:1.1em; white-space:nowrap; text-align:center;
}
.box .lbl.first {
  bottom:-44px; font-size:15px;
}

.box .val {
  position: absolute; left:4px; top:-28px; width:100%;
  padding: 0;
  color:#444; font-weight:normal; white-space: nowrap; text-align:center;
  z-index:1; /* fix "due" label overlapped by a taller "undue" bar */
}
.box .val-zero { color:#a09a8b; display:none; }

@media (max-width:767px) {
  .box_inner { height:170px; }
  #leitner-chart_pane { min-height:268px; }
}

@media screen and (max-width: 500px) {
  /* On small screen, hide the side, keep the top for a depth effect! */
  .box .bar .val  { left:0; }
  .box .bar .side { display:none; }
  .box .bar .top  { transform:none; }  /* front facing */
}

@media (min-width:767px) {
  /* increase bar thickness on larger displays */
  .box .bar .top { height:10px; }
  .box .bar .side { width:10px; }
  .box .bar .val { top:-32px; }
}

</style>