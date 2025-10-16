# Toast Notification System

**Version:** 2.1  
**Last Updated:** October 15, 2025  
**Status:** Production Ready ✅

---

## 🎯 Overview

The Toast Notification System provides elegant, non-intrusive notifications that slide in from the right side of the screen. The system follows Material Design 3 principles with a clean, minimal design that uses colored borders instead of background colors.

## 🎨 Design Features

### Visual Design

- **No background colors** - Clean, minimal appearance
- **Colored left border** - Visual distinction by type
- **Smaller size** - More compact and less intrusive
- **Material Design 3 colors** - Consistent with app design system
- **Enhanced shadows** - Better depth perception
- **Colored icons** - Icons match the border color

### Color System

- **Success**: Green border (`border-l-success-500`) + Green icon (`text-success-500`)
- **Error**: Red border (`border-l-error-500`) + Red icon (`text-error-500`)
- **Warning**: Yellow border (`border-l-warning-500`) + Yellow icon (`text-warning-500`)
- **Info**: Blue border (`border-l-primary-500`) + Blue icon (`text-primary-500`)

### Size & Spacing

- **Width**: `max-w-sm` (smaller than previous `max-w-md`)
- **Padding**: `px-4 py-3` (reduced from `px-6 py-4`)
- **Border**: `border-l-4` (left border accent)
- **Container spacing**: `space-y-2` (reduced from `space-y-3`)

## 🛠️ Implementation

### Components

#### Toast.vue

Main toast component with:

- Slide-in animation from right
- Auto-dismiss functionality
- Colored border and icon system
- Close button with hover effects

#### ToastContainer.vue

Container component that:

- Manages multiple toasts
- Handles positioning and spacing
- Provides pointer-events management

#### useToast.js Composable

Global composable providing:

- `success(message, duration)` - Green success toast
- `error(message, duration)` - Red error toast
- `warning(message, duration)` - Yellow warning toast
- `info(message, duration)` - Blue info toast
- `addToast(message, type, duration)` - Generic toast creation
- `removeToast(id)` - Manual toast removal

### Usage Examples

#### Basic Usage

```javascript
import { useToast } from '@/composables/useToast'

const toast = useToast()

// Success notification
toast.success('Account created successfully!')

// Error notification
toast.error('Failed to save changes')

// Warning notification
toast.warning('Please check your input')

// Info notification
toast.info('New feature available')
```

#### Custom Duration

```javascript
// Success toast that stays for 5 seconds
toast.success('Data saved!', 5000)

// Error toast that stays for 6 seconds
toast.error('Network error occurred', 6000)
```

#### Manual Control

```javascript
// Add toast and get ID for manual control
const toastId = toast.addToast('Processing...', 'info', 0) // 0 = no auto-dismiss

// Remove manually
toast.removeToast(toastId)
```

## 🎭 Animation & Behavior

### Slide Animation

- **Direction**: Slides in from right (`translate-x-full` → `translate-x-0`)
- **Duration**: 300ms with `ease-out` timing
- **Opacity**: Fades in/out with transform

### Auto-Dismiss

- **Success**: 3 seconds (3000ms)
- **Error**: 4 seconds (4000ms) - longer for important errors
- **Warning**: 3.5 seconds (3500ms)
- **Info**: 3 seconds (3000ms)

### Manual Dismiss

- Click the X button to close immediately
- Smooth fade-out animation (300ms)

## 🌙 Dark Mode Support

The toast system fully supports dark mode with:

- **Background**: `bg-white dark:bg-gray-800`
- **Text**: `text-gray-900 dark:text-gray-100`
- **Close button**: `text-gray-500 dark:text-gray-400`
- **Hover states**: Proper dark mode hover colors

## 🔧 Integration

### Session Flash Data

Toasts automatically display from Laravel session flash data:

```php
// In Laravel controller
return redirect()->back()->with('toast', [
    'message' => 'Settings saved successfully!',
    'type' => 'success'
]);
```

### Middleware Integration

The `HandleInertiaRequests` middleware automatically converts session flash data to toast props.

## 📱 Responsive Design

- **Mobile**: Toasts scale appropriately on small screens
- **Touch**: Close button has adequate touch target size
- **Spacing**: Container padding adjusts for mobile (`p-3`)

## 🧪 Testing

### Test Implementation

A temporary test section is available on the Dashboard page with buttons to test all toast types:

```vue
<!-- Test buttons for all toast types -->
<button @click="testSuccess">Test Success</button>
<button @click="testError">Test Error</button>
<button @click="testWarning">Test Warning</button>
<button @click="testInfo">Test Info</button>
```

### Manual Testing Checklist

- [ ] All toast types display correctly
- [ ] Colors match design system
- [ ] Animations work smoothly
- [ ] Auto-dismiss timing is appropriate
- [ ] Manual close works
- [ ] Dark mode colors are correct
- [ ] Mobile responsiveness
- [ ] Multiple toasts stack properly

## 🚀 Future Enhancements

### Planned Features

1. **Progress indicators** for long-running operations
2. **Action buttons** within toasts (e.g., "Undo" for delete operations)
3. **Rich content** support (images, links)
4. **Sound notifications** (optional)
5. **Toast history** for debugging

### Accessibility Improvements

1. **Screen reader** announcements
2. **Keyboard navigation** support
3. **High contrast** mode support
4. **Reduced motion** preferences

---

## 📋 Migration Notes

### From Previous Version

The toast system was updated from colored backgrounds to colored borders:

**Before:**

```css
bg-green-500 text-white  /* Solid green background */
```

**After:**

```css
bg-white dark:bg-gray-800 border-l-success-500 text-success-500  /* Clean with colored border */
```

### Breaking Changes

- None - the API remains the same
- Visual appearance changed but functionality preserved

---

**Last Updated:** October 15, 2025  
**Version:** 2.1  
**Maintainer:** Development Team
