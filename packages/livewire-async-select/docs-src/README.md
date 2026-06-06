---
home: true
heroImage: null
tagline: A powerful async select component for Laravel Livewire with Alpine.js
actionText: Get Started →
actionLink: /guide/
features:
- title: 🚀 Asynchronous Loading
  details: Load options dynamically from API endpoints with built-in search and filtering capabilities.
- title: 🎯 Multiple Selection
  details: Support for both single and multiple selection modes with beautiful chip/tag display.
- title: ⚡ Alpine.js Powered
  details: Lightweight and reactive with Alpine.js integration. No jQuery dependency required.
- title: 🎨 Theme Support
  details: Built-in support for Tailwind CSS and Bootstrap. Fully customizable styling.
- title: 🎭 Custom Slots
  details: Customize option and selected item rendering with powerful Blade slot support.
- title: 📦 Easy Integration
  details: Simple Livewire component integration with two-way binding and Laravel ecosystem.
footer: MIT Licensed | Copyright © 2025 Dr. Pshtiwan Mahmood
---

## 🎥 Demo Video

<div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; margin: 2rem 0;">
  <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" src="https://www.youtube.com/embed/xwfKgZu49gg" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
</div>

**[▶️ Watch on YouTube](https://www.youtube.com/watch?v=xwfKgZu49gg)**

## Quick Start

Install via Composer:

```bash
composer require drpshtiwan/livewire-async-select
```

Use in your Blade views:

```html
<livewire:async-select
    name="user_id"
    wire:model="selectedUser"
    :options="$users"
    placeholder="Select a user..."
/>
```

## Why Livewire Async Select?

Traditional select libraries like Select2 rely on jQuery and can be heavy and difficult to integrate with modern reactive frameworks. This package provides a native Livewire solution that:

- ✅ Works seamlessly with Livewire's reactive components
- ✅ Uses Alpine.js for lightweight interactivity
- ✅ Eliminates jQuery dependency
- ✅ Provides better integration with Laravel's ecosystem
- ✅ Offers cleaner, more maintainable code

## Features at a Glance

| Feature | Livewire Async Select | Select2 |
|---------|----------------------|---------|
| jQuery Dependency | ❌ No | ✅ Yes |
| Livewire Integration | ✅ Native | ⚠️ Requires workarounds |
| Bundle Size | 🟢 Small (~10KB) | 🟡 Large (~60KB+) |
| Alpine.js | ✅ Yes | ❌ No |
| Modern Stack | ✅ Yes | ❌ Legacy |
| Two-way Binding | ✅ Native | ⚠️ Manual |
| Laravel Integration | ✅ First-class | 🟡 Generic |
| Collection Support | ✅ Yes | ❌ No |
| Built-in Validation | ✅ Yes | ⚠️ Manual |

[Get Started →](/guide/)  
[Full Comparison with Select2 →](/guide/select2-comparison.html)

