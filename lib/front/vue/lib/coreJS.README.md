# CoreJS

## Cheatsheet

```js
// selection
$(“#container”)
$(“#container”, rootElement)

// from array
$(arrayOfElements)

// events (events can be string or array)
.on('click', fn)
.on(['mouseenter', 'mouseleave'], fn)
.off()
.off('click')
.off(['click', 'mouseleave'])
.off(fn)

// call listener once, then remvove it
.once('transitionend', fn)

// applies Array.prototype.forEach() (works on non Arrays)
.each(fn)

// misc
.includes(searchElement)
.includes(searchElement, fromIndex)

```

## Styles

Properties can be camelCase or kebab-case. No special handling by coreJS here since you could write it directly like this: `element.style['background-color']` and `element.style.backgroundColor`. Meaning most times, you might like to just use kebab-case which is consistent with how you'd write a css rule.

```js
// get one style
.css("property")

// set one style
.css("property", "value")

// set one or more styles via object notation
.css({
  "display": "block",
  "background-color": "red"
})

```


## Iterating

`.each(fn)`
Applies the native *Array.prototype.forEach* to the selection.

```js
// just the element
$("main-menu li").each( el =>  { ... } )

// uses native forEach() callback parameters
$("main-menu li").each( (currentValue, index, array) => { ... } )
```
