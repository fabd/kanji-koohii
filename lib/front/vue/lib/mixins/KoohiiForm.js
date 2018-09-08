/**
 * A mixin that helps handle the POST cycle of ajax forms in Vue components
 *
 *
 *   koohiiformGetErrors()    returns html for errors, or false (use with v-if, and v-html)
 *
 *   koohiiformHandleResponse(tron)    call once from an ajax response handler, to set up the form errors
 *
 */

export default {

  data: function() {
    return {
      koohiiformErrors: []
    }
  },

  computed: {
    koohiiformGetErrors() {
      return this.koohiiformErrors.length
        ? `<span>${ this.koohiiformErrors.join('</span><span>') }</span>`
        : false;
    },
  },

  methods: {

    koohiiformHandleResponse(tron) {
      this.koohiiformErrors = tron.getErrors()
    }
  }
}
