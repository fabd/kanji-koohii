<template>
  <div class="">
    <h2>Buttons</h2>

    <p>
      element-ui buttons using
      <code>type="primary"</code> (etc).
    </p>

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

    <p>Vanilla html buttons (eg. used in php templates)</p>

    <section class="ux-docSection ux-docDemoblock">
      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">Size: small</span>
        <button class="ko-Btn ko-Btn--small">Default</button>
        <button class="ko-Btn ko-Btn--small ko-Btn--primary">Primary</button>
        <button class="ko-Btn ko-Btn--small ko-Btn--success">Success</button>
        <button class="ko-Btn ko-Btn--small ko-Btn--danger">Danger</button>
      </div>

      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">Size: default</span>
        <button class="ko-Btn">Default</button>
        <button class="ko-Btn ko-Btn--primary">Primary</button>
        <button class="ko-Btn ko-Btn--success">Success</button>
        <button class="ko-Btn ko-Btn--danger">Danger</button>
      </div>

      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">Size: large</span>
        <button class="ko-Btn ko-Btn--large">Default</button>
        <button class="ko-Btn ko-Btn--large ko-Btn--primary">Primary</button>
        <button class="ko-Btn ko-Btn--large ko-Btn--success">Success</button>
        <button class="ko-Btn ko-Btn--large ko-Btn--danger">Danger</button>
      </div>

      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">is-ghost</span>
        <button class="ko-Btn is-ghost ko-Btn--large">Default</button>
        <button class="ko-Btn is-ghost ko-Btn--large ko-Btn--primary"
          >Primary</button
        >
        <button class="ko-Btn is-ghost ko-Btn--large ko-Btn--success"
          >Success</button
        >
        <button class="ko-Btn is-ghost ko-Btn--large ko-Btn--danger"
          >Danger</button
        >
      </div>

      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">is-disabled</span>
        <button class="ko-Btn is-disabled ko-Btn--large" disabled
          >Default</button
        >
        <button
          class="ko-Btn is-disabled ko-Btn--large ko-Btn--primary"
          disabled
          >Primary</button
        >
        <button
          class="ko-Btn is-disabled ko-Btn--large ko-Btn--success"
          disabled
          >Success</button
        >
        <button class="ko-Btn is-disabled ko-Btn--large ko-Btn--danger" disabled
          >Danger</button
        >
      </div>
    </section>

    <p>Example buttons with icons</p>

    <section class="ux-docSection ux-docDemoblock">
      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">&lt;button&gt;</span>

        <button
          type="button"
          class="ko-Btn ko-Btn--danger ko-Btn--large"
          disabled
        >
          Begin Restudy
          <i class="fa fa-book-open ml-2"></i>
        </button>

        <button class="ko-Btn ko-Btn--large ko-Btn--primary">
          Spaced Repetition
          <i class="fa fa-arrow-right ml-2"></i>
        </button>
        <button
          class="ko-Btn ko-Btn--large ko-Btn--success is-disabled"
          disabled
        >
          Start Review
          <i class="fa fa-arrow-right ml-2"></i>
        </button>
        <button class="ko-Btn is-ghost ko-Btn--large ko-Btn--primary">
          Show Kanji
          <i class="fa fa-chevron-down ml-2"></i>
        </button>
      </div>

      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">&lt;a&gt;</span>
        <a href="#" class="ko-Btn is-ghost rounded">
          <i class="fa fa-edit mr-2"></i>Edit Card
        </a>
      </div>
    </section>
  </div>
</template>
