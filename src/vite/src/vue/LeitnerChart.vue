<template>
  <div>
    <transition name="chart-fade" appear>
      <div v-once class="pt-[30px] pb-[40px] flex justify-between">
        <div v-for="(box, b) in displayBoxes" :key="b" class="box flex-1">
          <div class="box_inner relative h-[170px] md:h-[200px]">
            <div
              :class="{ lbl: true, first: b === 0 }"
              v-html="getBoxLabel(b)"
            ></div>
            <a
              href="#"
              class="bar bar1"
              :style="{
                height: getHeight(box[0]),
                backgroundColor: getColor(box[0], 0),
              }"
              @click="onClick($event, box[0])"
            >
              <div
                class="side"
                :style="{ backgroundColor: getColor(box[0], 1) }"
              ></div>
              <div
                class="top"
                :style="{ backgroundColor: getColor(box[0], 2) }"
              ></div>
              <span :class="['val', box[0].value ? '' : 'val-zero']">{{
                box[0].value
              }}</span>
            </a>
            <a
              href="#"
              class="bar bar2"
              :style="{
                height: getHeight(box[1]),
                backgroundColor: getColor(box[1], 0),
              }"
              @click="onClick($event, box[1])"
            >
              <div
                class="side"
                :style="{ backgroundColor: getColor(box[1], 1) }"
              ></div>
              <div
                class="top"
                :style="{ backgroundColor: getColor(box[1], 2) }"
              ></div>
              <span :class="['val', box[1].value ? '' : 'val-zero']">{{
                box[1].value
              }}</span>
            </a>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";

export default defineComponent({
  props: {
    // id of a parent container used to determine the available horizontal space
    containerId: { type: String, required: true },

    chartData: { type: Object as PropType<TLeitnerChart>, required: true },
  },

  data() {
    return {
      colors: {
        // front, side & top colors for each type of stack
        failed: ["#ff8257", "#d2633f", "#ffa994"],
        new: ["#40a8e5", "#3d83ac", "#8abde4"],
        fresh: ["#40e569", "#3dac58", "#8ae49c"],
        due: ["#ffae57", "#d2633f", "#ffcc7f"],
        nill: ["#929292", "#818181", "#b1b1b1"],
      },
    };
  },

  computed: {
    // less than ideal solution due to device orientation switch
    numDisplayBoxes(): number {
      let numSrsBoxes = this.chartData.boxes.length;

      let el,
        containerWidth =
          ((el = document.getElementById(this.containerId)) &&
            el.offsetWidth) ||
          0;

      // console.log('container width: %d     num boxes: %d', containerWidth, numSrsBoxes)

      let isWide = containerWidth >= 500;

      let maxVisibleBoxes = isWide ? 11 : 8;

      return Math.min(maxVisibleBoxes, numSrsBoxes);
    },

    displayBoxes(): TLeitnerBox[] {
      let maxBox = this.numDisplayBoxes,
        boxes = [] as TLeitnerBox[];

      this.chartData.boxes.map((box, i) => {
        if (i < maxBox) {
          boxes.push(box);
        } else {
          boxes[maxBox - 1][0].value += box[0].value;
          boxes[maxBox - 1][1].value += box[1].value;
        }
      });

      return boxes;
    },

    maxHeight(): number {
      let stacks = this.displayBoxes.flat();
      let counts = stacks.map((s) => s.value);
      return Math.max(...counts);
    },
  },

  beforeMount() {
    this.prepareStacks();
  },

  methods: {
    getBoxLabel(box: number) {
      const lastBox = this.numDisplayBoxes - 1;
      return box === 0 ? "Fail &<br>New" : box < lastBox ? box : `${box}+`;
    },

    getColor(bar: TLeitnerStack, side: number) {
      return this.colors[bar.type][side];
    },

    toPercent(height: number) {
      return this.maxHeight ? Math.ceil((height * 100) / this.maxHeight) : 0;
    },

    getHeight(bar: TLeitnerStack) {
      var height = this.toPercent(bar.value);

      return height < 4 ? "4px" : `${height}%`;
    },

    onClick(event: Event, bar: TLeitnerStack) {
      let url = this.getBarUrl(bar);

      // console.log('bar %d  type %s   go to %s' , bar.index, bar.type, url)
      if (url !== "") {
        window.location.href = url;
        return true;
      }

      event.preventDefault();
      return false;
    },

    getBarUrl(bar: TLeitnerStack) {
      let url = "";
      if (bar.value && bar.index < 2) {
        url = this.chartData.urls[bar.index === 0 ? "restudy" : "new"];
      } else if (bar.value && bar.type === "due") {
        url = this.chartData.urls["due"] + "&box=" + ((bar.index >> 1) + 1);
      }
      return url;
    },

    getBarType(bar: TLeitnerStack) {
      let i = bar.index,
        type: TLeitnerStackId;

      if (!bar.value) {
        type = "nill";
      } else if (i < 2) {
        type = i & 1 ? "new" : "failed";
      } else {
        type = i & 1 ? "fresh" : "due";
      }

      return type;
    },

    prepareStacks() {
      this.displayBoxes.forEach((box, index) => {
        box[0].index = index * 2;
        box[0].type = this.getBarType(box[0]);

        box[1].index = index * 2 + 1;
        box[1].type = this.getBarType(box[1]);
      });
    },
  },
});
</script>

<style lang="scss">
@import "@/assets/sass/components/_LeitnerChart.scss";
</style>
