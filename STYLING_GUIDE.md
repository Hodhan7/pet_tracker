# Pet Health Tracker - Styling Guide

## Overview
This Pet Health Tracker application has been enhanced with a comprehensive styling system using Tailwind CSS and custom components to provide a modern, professional, and user-friendly interface.

## Design System

### Color Palette
- **Primary Blue**: Used for main actions, navigation, and primary buttons
- **Secondary Green**: Used for success states, veterinary-related actions
- **Accent Yellow**: Used for warnings and highlights
- **Grays**: Used for text, borders, and backgrounds

### Typography
- **Font Family**: Inter (Google Fonts) - clean, modern, and highly legible
- **Font Weights**: 300 (Light), 400 (Regular), 500 (Medium), 600 (SemiBold), 700 (Bold)

### Components

#### Buttons (`.btn`)
- **Primary** (`.btn-primary`): Blue background, white text
- **Secondary** (`.btn-secondary`): Green background, white text
- **Outline** (`.btn-outline`): Transparent background, blue border
- **Danger** (`.btn-danger`): Red background, white text

#### Cards (`.card`)
- **Basic Card** (`.card`): White background, rounded corners, subtle shadow
- **Card Header** (`.card-header`): Gray background, bottom border
- **Card Body** (`.card-body`): Main content area with padding
- **Card Footer** (`.card-footer`): Gray background, top border

#### Forms
- **Form Group** (`.form-group`): Wrapper for form fields
- **Form Label** (`.form-label`): Consistent label styling
- **Form Input** (`.form-input`): Text inputs with focus states
- **Form Select** (`.form-select`): Dropdown selects
- **Form Textarea** (`.form-textarea`): Multi-line text areas

#### Alerts
- **Success** (`.alert-success`): Green background for success messages
- **Error** (`.alert-error`): Red background for error messages
- **Warning** (`.alert-warning`): Yellow background for warnings
- **Info** (`.alert-info`): Blue background for information

#### Tables
- **Table** (`.table`): Clean table styling with hover effects
- **Table Header** (`.table-header`): Header row styling
- **Table Cell** (`.table-cell`): Standard cell styling

### Pet-Specific Components

#### Pet Cards (`.pet-card`)
- Left border accent color
- Hover effects with subtle elevation
- Avatar with pet's first initial

#### Appointment Status
- **Pending** (`.status-pending`): Yellow background
- **Confirmed** (`.status-confirmed`): Green background
- **Completed** (`.status-completed`): Blue background
- **Cancelled** (`.status-cancelled`): Red background

#### Health Records (`.health-record`)
- Green left border accent
- Gradient background
- Structured metric display

### Dashboard Features

#### Statistics Cards (`.dashboard-stat`)
- Large number display for key metrics
- Icon integration
- Hover effects

#### Navigation (`.navbar`)
- Fixed header with brand and user info
- Mobile-responsive design

### Animations

#### CSS Animations
- **Fade In** (`.animate-fade-in`): Smooth opacity transition
- **Slide Up** (`.animate-slide-up`): Element slides up on load
- **Loading Spinner** (`.loading-spinner`): Rotating loading indicator

#### JavaScript Enhancements
- Staggered card animations on page load
- Hover effects on interactive elements
- Auto-hiding alert messages
- Form validation with visual feedback
- Loading states for form submissions

### Responsive Design

#### Breakpoints
- **Mobile**: Up to 768px
- **Tablet**: 768px - 1024px
- **Desktop**: 1024px and above

#### Mobile Optimizations
- Stacked navigation menu
- Single-column layouts for cards and statistics
- Touch-friendly button sizes
- Optimized spacing and typography

### File Structure

```
css/
├── tailwind.css          # Compiled Tailwind CSS with custom components
js/
├── main.js              # Interactive JavaScript functionality
input.css                # Source Tailwind CSS file
tailwind.config.js       # Tailwind configuration
```

### Usage Examples

#### Creating a Card
```html
<div class="card">
    <div class="card-header">
        <h2 class="text-xl font-bold">Card Title</h2>
    </div>
    <div class="card-body">
        <p>Card content goes here</p>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary">Action</button>
    </div>
</div>
```

#### Form Structure
```html
<form class="space-y-6">
    <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" class="form-input" required>
    </div>
    <button type="submit" class="btn btn-primary w-full">Submit</button>
</form>
```

#### Alert Messages
```html
<div class="alert alert-success">
    <svg class="w-4 h-4 mr-2"><!-- icon --></svg>
    Success message here
</div>
```

### Build Process

To compile the CSS from source:

```bash
npm install
npm run build
```

For development with watch mode:

```bash
npm run build-css
```

### Browser Support
- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+

### Accessibility Features
- High contrast colors for WCAG compliance
- Focus indicators for keyboard navigation
- Semantic HTML structure
- Screen reader friendly markup
- Proper form labeling

### Performance Optimizations
- CSS animations use transform and opacity for better performance
- Minimal JavaScript for core functionality
- Optimized font loading with Google Fonts
- Efficient CSS selectors

This styling system provides a solid foundation for the Pet Health Tracker application while maintaining flexibility for future enhancements and customizations.
