<template>
  <div class="">

    <h2>What</h2>
    <section class="ux-docSection ux-docDemoblock">
    <p>
      This is a live playground / docs for the components and helpers and whatnot.
    </p>
    </section>

    <h2>Development Notes</h2>
    
    <section class="ux-docSection ux-docDemoblock markdown">
      <h3>Css Guidelines</h3>
      <ul>
        <li>generally we use a BEM-like approach for naming classes, based on <a href="https://github.com/suitcss/suit/blob/master/doc/naming-conventions.md">SuitCSS</a> (<a href="https://github.com/suitcss/suit/issues/80#issuecomment-46094932">why not BEM</a>)</li>
        <li>... we use `ko-` pefix for to namespace component styles, eg. `ko-Box`</li>
        <li>... we use `ux-` prefix to namespace the utilities, eg. `ux-text-md`</li>
        <li>... there is a lot of legacy CSS going all the way back to 2006 that does not follow these conventions, it is replaced/rewritten little by little over time</li>
        <li>state aka "modifier" classes use `is-` prefix, eg. `is-active`</li>
        <li>whenever we need a hook between JS and CSS we prefix the class with `Js`, eg `&lt;div id="JsMyComponent"...`. This helps to find related code and template, and refactor. These classes should NOT be styled with CSS</li>
        <li>preferably keep the component styles under @/assets/sass/, since components styles are not truly independent, but rely on a global stylesheet as well as
          global tokens and css variables, it's easier to maintain and edit files
          when all the stylesheets are grouped together
        </li>
      </ul>
    </section>

  </div>
</template>