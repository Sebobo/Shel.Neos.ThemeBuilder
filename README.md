# Theme builder for Neos CMS

WIP Neos CMS theme builder package. Currently in development.

## Features

* Define theme properties by extending a common NodeType mixin with property presets
* Set theme properties in pages inheriting this mixin
* Use theme properties in other elements via property presets using an included data source
* Merged CSS variables for the current page are automatically rendered into the head of the document

## Helpers

### Generate CSS from props for style attributes

Define styles f.e. as privat prop in a component and use it in the AFX template:

```
@private.myStyle = Shel.Neos.ThemeBuilder:Helper.Styles {
    --my-prop = '20px'
    --my-prop-2 = ${props.myStyleProp}
    color = 'blue'
}
```

```
<div style={private.myStyle}>Some test</div>
```

Empty values will be filtered out.

## Contributions

Contributions are very welcome! 

Please create detailed issues and PRs.
