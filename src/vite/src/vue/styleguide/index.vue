<template>
  <div class="p-8">
    <h1><a href="/index_dev.php">Koohii</a> / UX Docs</h1>

    <h2>Buttons</h2>

    <h3>Vanilla element-ui buttons</h3>

    <p>Vanilla element-ui buttons using <code>type="primary"</code> (etc).</p>

    <section class="ux-docSection ux-docDemoblock">
      <div class="mb-2">
        <el-button>Default</el-button>
        <el-button type="primary">Primary</el-button>
        <el-button type="success">Success</el-button>
        <el-button type="info">Info</el-button>
        <el-button type="warning">Warning</el-button>
        <el-button type="danger">Danger</el-button>
      </div>

      <div class="mb-2">
        <el-button round>Round</el-button>
        <el-button type="primary" round>Primary</el-button>
        <el-button type="success" round>Success</el-button>
        <el-button type="info" round>Info</el-button>
        <el-button type="warning" round>Warning</el-button>
        <el-button type="danger" round>Danger</el-button>
      </div>
    </section>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

import { ElButton } from "@lib/vendor/element";

export default defineComponent({
  name: "StyleguideIndex",
  components: {
    ElButton,
  },
});
</script>

<style lang="scss"></style>
