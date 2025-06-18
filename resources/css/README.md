# Nutriplátanos Design System

A comprehensive design system for the Nutriplátanos application built with Tailwind CSS 4.x and Laravel.

## Overview

This design system provides a consistent visual language and reusable components that reflect the brand identity of Nutriplátanos - combining the vibrant yellow of bananas with the fresh green of nature.

## Design Tokens

### Brand Colors

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-primary-500` | `#f5e50a` | Primary brand yellow (banana) |
| `--color-secondary-500` | `#178d35` | Secondary brand green (leaf) |

#### Available Shades
- **Primary (Banana)**: 50-900 (lightest to darkest)
- **Secondary (Leaf)**: 50-900 (lightest to darkest)

### Typography

| Size | Token | Value | Usage |
|------|-------|-------|-------|
| XS | `--font-size-xs` | 12px | Small labels, captions |
| SM | `--font-size-sm` | 14px | Body text, buttons |
| Base | `--font-size-base` | 16px | Default body text |
| LG | `--font-size-lg` | 18px | Large body text |
| XL | `--font-size-xl` | 20px | Small headings |
| 2XL | `--font-size-2xl` | 24px | Medium headings |
| 3XL | `--font-size-3xl` | 30px | Large headings |
| 4XL | `--font-size-4xl` | 36px | Hero headings |

### Spacing

The design system uses an 8pt grid system for consistent spacing:

| Token | Value | Usage |
|-------|-------|-------|
| `--spacing-1` | 4px | Minimal spacing |
| `--spacing-2` | 8px | Small spacing |
| `--spacing-4` | 16px | Base spacing |
| `--spacing-6` | 24px | Medium spacing |
| `--spacing-8` | 32px | Large spacing |
| `--spacing-12` | 48px | Extra large spacing |

## Components

### Buttons

#### Primary Button
```html
<button class="btn btn-primary">Primary Action</button>
```

#### Secondary Button
```html
<button class="btn btn-secondary">Secondary Action</button>
```

#### Outline Button
```html
<button class="btn btn-outline">Outline Action</button>
```

#### Ghost Button
```html
<button class="btn btn-ghost">Ghost Action</button>
```

#### Size Variants
```html
<button class="btn btn-primary btn-sm">Small</button>
<button class="btn btn-primary">Default</button>
<button class="btn btn-primary btn-lg">Large</button>
```

### Form Elements

#### Input Field
```html
<div>
    <label class="form-label" for="email">Email</label>
    <input class="form-input" type="email" id="email" placeholder="Enter your email">
    <div class="form-error">This field is required</div>
</div>
```

### Cards

#### Basic Card
```html
<div class="card">
    <div class="card-header">
        <h3>Card Title</h3>
    </div>
    <div class="card-body">
        <p>Card content goes here.</p>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">Action</button>
    </div>
</div>
```

### Status Badges

```html
<span class="badge badge-success">Success</span>
<span class="badge badge-warning">Warning</span>
<span class="badge badge-error">Error</span>
<span class="badge badge-info">Info</span>
```

### Navigation

```html
<nav>
    <a href="#" class="nav-link active">Dashboard</a>
    <a href="#" class="nav-link">Inventory</a>
    <a href="#" class="nav-link">Routes</a>
    <a href="#" class="nav-link">Sales</a>
</nav>
```

## Tailwind Classes

### Brand Colors
- `bg-banana-{50-900}` - Background colors using banana yellow scale
- `text-banana-{50-900}` - Text colors using banana yellow scale
- `bg-leaf-{50-900}` - Background colors using leaf green scale  
- `text-leaf-{50-900}` - Text colors using leaf green scale

### Utilities
- `.text-brand-primary` - Primary brand color text
- `.text-brand-secondary` - Secondary brand color text
- `.bg-brand-primary` - Primary brand color background
- `.bg-brand-secondary` - Secondary brand color background
- `.surface` - Standard surface background
- `.surface-elevated` - Elevated surface with shadow
- `.interactive` - Interactive element with hover states
- `.focus-ring` - Standard focus ring styles

## Usage Guidelines

### Color Usage
- **Primary Yellow**: Use for primary actions, highlights, and brand emphasis
- **Secondary Green**: Use for success states, nature-related elements, and secondary actions
- **Neutral Grays**: Use for text, borders, and backgrounds

### Typography Hierarchy
1. Use consistent font sizes from the scale
2. Maintain proper contrast ratios (4.5:1 minimum for body text)
3. Use font weights purposefully (normal for body, medium/bold for emphasis)

### Spacing
- Follow the 8pt grid system
- Use consistent spacing throughout the application
- Group related elements with smaller spacing
- Separate unrelated sections with larger spacing

### Interactive Elements
- All interactive elements should have focus states
- Use consistent hover effects
- Provide visual feedback for user actions

## File Structure

```
resources/css/
├── app.css                    # Main CSS file (current)
├── design-system.css          # Enhanced design system (new)
└── components/                # Component-specific styles (optional)
    ├── buttons.css
    ├── forms.css
    └── cards.css
```

## Migration from Current app.css

1. **Keep your current app.css** for immediate compatibility
2. **Gradually adopt design-system.css** by importing it alongside your current styles
3. **Replace custom styles** with design system classes over time
4. **Update components** to use the new component classes

## Best Practices

1. **Use CSS Custom Properties**: Always prefer CSS variables over hardcoded values
2. **Follow BEM naming**: For custom components not covered by the design system
3. **Maintain consistency**: Use design tokens instead of arbitrary values
4. **Test accessibility**: Ensure proper contrast and focus states
5. **Mobile-first**: Design for mobile and enhance for larger screens

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)  
- Safari (latest 2 versions)
- iOS Safari (latest 2 versions)
- Android Chrome (latest 2 versions)

## Contributing

When adding new components or tokens:

1. Follow the existing naming conventions
2. Add documentation with examples
3. Ensure accessibility compliance
4. Test across supported browsers
5. Update this documentation
