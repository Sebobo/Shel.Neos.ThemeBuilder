# Theme builder for Neos CMS

This is a Neos CMS plugin for managing themes with CSS variables from the backend.

By defining theme properties in a NodeType mixin, these can be configured on the homepage or 
subpages and used to globally control your projects look & feel, or to allow choosing styles for individual
elements from the defined properties.
Think of defining various colors, spacings, font sizes, etc. in a theme and using them in your project.

This results in a flexible way to provide freedom and yet consistency in the design of your project.

## Features

* Define theme properties by extending a common NodeType mixin with property presets
* Set theme properties in pages inheriting this mixin
* Use theme properties in other elements via property presets using an included data source
* Merged CSS variables for the current page are automatically rendered into the head of the document

## Installation

Install the package via composer in your site package:

```console
composer require --no-update shel/neos-theme-builder
```

Then run `composer update shel/neos-theme-builder` in your project root.

## Usage

### Define theme properties

Add additional properties to your theme mixin NodeType:

```yaml
Shel.Neos.ThemeBuilder:Mixin.PageTheme:
    properties:
        primaryColor:
            options:
                preset: 'themeBuilder.colorPicker'
            ui:
                label: 'Primary color'
                inspector:
                    group: 'themeMain'
```

Then extend your page NodeType with this mixin:

```yaml
'Vendor.Site:Document.Page':
  superTypes:
    'Shel.Neos.ThemeBuilder:Mixin.PageTheme': true
```

Now you can set the primary color in the inspector of your page and use the 
value in your project via CSS variable `var(--primary-color)`.
You can find all generated CSS variables in the head of your document contained in a style tag.

### Use theme properties in other elements

You can use the theme properties in other elements by using the `themeBuilder.colorSelector` preset.

Example:

```yaml
'Vendor.Site:Content.MyComponent':
  properties:
    color:
      options:
          preset: 'themeBuilder.colorSelector'
      ui:
        label: 'Color'
```

This will render a select box in the inspector with all available colors from the theme properties.

## Helpers

### Generate CSS from props for style attributes

Define styles f.e. as private prop in a component and use it in the AFX template:

```neosfusion
prototype(My.Vendor:Content.MyComponent) < prototype(Neos.Fusion:Component) {
    myStyleProp = 'red'
    
    @private.style = Shel.Neos.ThemeBuilder:Helper.Styles {
        --my-prop = '20px'
        --my-prop-2 = ${props.myStyleProp}
        color = 'blue'
    }
    
    renderer = afx`
        <div style={private.style}>Some test</div>
    `
}
```

This will render the following HTML:

```html
<div style="--my-prop: 20px; --my-prop-2: red; color: blue;">Some test</div>
```

Note: Empty or `null` values will be filtered out automatically, so you don't need conditions for standard cases.

## Contributions

Contributions are very welcome! 

Please create detailed issues and PRs.
