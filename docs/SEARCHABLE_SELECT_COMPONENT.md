# SearchableSelect Component

**Version:** 1.0  
**Created:** October 10, 2025  
**Location:** `/resources/js/Components/SearchableSelect.vue`

---

## 📖 Overview

`SearchableSelect` is a reusable Vue 3 component that provides an enhanced dropdown select with built-in search functionality. Perfect for lists with many options (like timezones, countries, languages, etc.).

---

## ✨ Features

- 🔍 **Real-time search** - Filter options as you type
- 📁 **Grouped options** - Organize options into categories
- 🎨 **Dark mode support** - Fully styled for light and dark themes
- ♿ **Accessible** - Keyboard navigation and ARIA support
- 🎯 **Click outside to close** - Intuitive UX
- ✅ **Selected indicator** - Visual feedback for selected option
- 🧹 **Clear search** - Quick clear button in search field
- 💅 **Beautiful UI** - Modern, clean design with smooth transitions

---

## 📦 Usage

### Basic Example

```vue
<script setup>
import SearchableSelect from '@/Components/SearchableSelect.vue'
import { ref } from 'vue'

const selected = ref('UTC')

const timezones = {
    'UTC': 'UTC',
    'America/New_York': 'New York (Eastern Time)',
    'America/Los_Angeles': 'Los Angeles (Pacific Time)',
    'Europe/London': 'London',
    'Asia/Tokyo': 'Tokyo',
}
</script>

<template>
    <SearchableSelect
        v-model="selected"
        :options="timezones"
        placeholder="Select timezone..."
        search-placeholder="Search timezones..."
    />
</template>
```

---

### Grouped Options Example

```vue
<script setup>
import SearchableSelect from '@/Components/SearchableSelect.vue'
import { ref, computed } from 'vue'

const selected = ref('America/New_York')

const commonTimezones = {
    'UTC': 'UTC',
    'America/New_York': 'New York (Eastern Time)',
    'America/Los_Angeles': 'Los Angeles (Pacific Time)',
}

const allTimezones = {
    'UTC': 'UTC',
    'America/New_York': 'New York (Eastern Time)',
    'America/Chicago': 'Chicago (Central Time)',
    'America/Los_Angeles': 'Los Angeles (Pacific Time)',
    'Europe/London': 'London',
    'Europe/Paris': 'Paris',
    'Asia/Tokyo': 'Tokyo',
    'Asia/Shanghai': 'Shanghai',
}

const groupedTimezones = computed(() => ({
    '🌟 Common': commonTimezones,
    '🌍 All Timezones': allTimezones,
}))
</script>

<template>
    <SearchableSelect
        v-model="selected"
        :options="allTimezones"
        :grouped-options="groupedTimezones"
        placeholder="Select timezone..."
        search-placeholder="Search timezones..."
    />
</template>
```

---

## 🎛️ Props

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `modelValue` | `String\|Number` | ✅ Yes | - | Selected value (v-model) |
| `options` | `Object` | ✅ Yes | - | All options as `{ value: label }` |
| `groupedOptions` | `Object` | ❌ No | `null` | Grouped options as `{ groupName: { value: label } }` |
| `placeholder` | `String` | ❌ No | `'Select an option...'` | Placeholder for closed state |
| `searchPlaceholder` | `String` | ❌ No | `'Search...'` | Placeholder for search input |
| `disabled` | `Boolean` | ❌ No | `false` | Disable the select |
| `error` | `String` | ❌ No | `null` | Error message (changes border color) |

---

## 📤 Events

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | `String\|Number` | Emitted when selection changes |

---

## 🎨 Styling

The component uses Tailwind CSS classes and supports:
- ✅ Light mode
- ✅ Dark mode
- ✅ Error states
- ✅ Disabled states
- ✅ Hover effects
- ✅ Focus states
- ✅ Smooth transitions

---

## 🔧 Advanced Features

### Search Algorithm

The search filters both:
- Option **values** (e.g., `America/New_York`)
- Option **labels** (e.g., `New York (Eastern Time)`)
- Group **names** (e.g., `Common`)

### Keyboard Support

- `Escape` - Close dropdown
- Click outside - Close dropdown
- Type to search - Real-time filtering

### Accessibility

- Proper ARIA labels
- Keyboard navigation
- Focus management
- Screen reader friendly

---

## 💡 Real-World Examples

### 1. Timezone Selector (Current Implementation)

```vue
<SearchableSelect
    v-model="form.timezone"
    :options="allTimezones"
    :grouped-options="groupedTimezones"
    :placeholder="t('profile.information.timezone')"
    :search-placeholder="t('profile.information.search_timezone')"
    :error="form.errors.timezone"
/>
```

### 2. Country Selector

```vue
<SearchableSelect
    v-model="form.country"
    :options="countries"
    placeholder="Select country..."
    search-placeholder="Search countries..."
/>
```

### 3. Language Selector

```vue
<SearchableSelect
    v-model="form.language"
    :options="languages"
    :grouped-options="{
        'Common': commonLanguages,
        'All Languages': allLanguages
    }"
    placeholder="Select language..."
    search-placeholder="Search languages..."
/>
```

### 4. Category Selector

```vue
<SearchableSelect
    v-model="selectedCategory"
    :options="categories"
    placeholder="Select category..."
    search-placeholder="Search categories..."
    :disabled="isLoading"
/>
```

---

## 🧪 Testing

The component is tested as part of the timezone feature tests:

```bash
php artisan test --filter=TimezoneTest
```

---

## 📝 Best Practices

1. **Use grouped options** for lists with >20 items
2. **Provide clear search placeholders** to guide users
3. **Use error prop** for validation feedback
4. **Keep option labels concise** but descriptive
5. **Use disabled state** during async operations

---

## 🚀 Future Enhancements

Potential improvements:
- [ ] Multi-select support
- [ ] Custom option templates
- [ ] Async option loading
- [ ] Virtual scrolling for very long lists
- [ ] Option to show/hide selected checkmarks
- [ ] Custom icons per option

---

## 🐛 Known Limitations

1. Options must be objects with `value: label` structure
2. No multi-select (single selection only)
3. No option icons/avatars (text only)

---

## 📚 Related Documentation

- [Timezone Feature Documentation](./TIMEZONE_FEATURE.md)
- [Component Architecture](./COMPONENT_ARCHITECTURE.md)
- [UI/UX Guidelines](./UI_UX_GUIDELINES.md)

---

**Last Updated:** October 10, 2025  
**Maintainer:** Development Team

