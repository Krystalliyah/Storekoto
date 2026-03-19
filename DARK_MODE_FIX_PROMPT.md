# Dark Mode Fix - Complete Component Background Issue

## Problem Description
The dark mode implementation has several issues where white backgrounds persist in dark mode, making text invisible or creating visual inconsistencies:

1. **Profile Page**: The main profile card and sub-sections (Profile Information, Account Actions) show as one continuous greyish-blue background instead of separate dark cards
2. **Inventory Page**: Stat cards at the top remain white with invisible text
3. **Orders Page**: Order management cards remain white 
4. **Expenses/Analytics Pages**: Stat cards remain white with invisible icons

## Current State
- Light mode: Green sidebar/header with white content cards ✅ (working correctly)
- Dark mode: Should have dark sidebar/header with dark content cards ❌ (partially working)

## Required Solution
Fix the Vue components directly by:

### 1. Profile Page (`resources/js/pages/vendor/Profile.vue`)
- Make each Card component have distinct dark backgrounds in dark mode
- Ensure proper separation between profile card, profile information card, and account actions card
- Fix text visibility in all sections

### 2. Inventory Page (`resources/js/pages/vendor/Inventory.vue`) 
- Convert white stat cards to dark backgrounds
- Make icon backgrounds gold-tinted in dark mode
- Ensure all text is visible

### 3. Orders Page (`resources/js/pages/vendor/Orders.vue`)
- Convert white order management cards to dark backgrounds
- Fix status badges and text visibility

### 4. Expenses/Analytics Pages
- Convert white stat cards to dark backgrounds  
- Make icon backgrounds gold-tinted (rgba(212, 183, 106, 0.15))
- Keep icon colors but change their container backgrounds

## Implementation Approach
Instead of relying on CSS overrides, modify the Vue components directly to:

1. **Add conditional classes**: Use `class="bg-white dark:bg-slate-800"` pattern
2. **Update text colors**: Use `text-gray-900 dark:text-white` pattern  
3. **Fix icon containers**: Use `bg-blue-50 dark:bg-amber-100/15` pattern
4. **Ensure proper borders**: Use `border-gray-200 dark:border-gray-700` pattern

## Color Scheme
- **Light mode**: Keep existing green theme (#245C4A, #1B4D3E)
- **Dark mode**: 
  - Cards: `#1e293b` (slate-800)
  - Text: `#f1f5f9` (slate-100) 
  - Icon backgrounds: `rgba(212, 183, 106, 0.15)` (gold tint)
  - Borders: `#374151` (gray-700)

## Files to Modify
1. `resources/js/pages/vendor/Profile.vue`
2. `resources/js/pages/vendor/Inventory.vue` 
3. `resources/js/pages/vendor/Orders.vue`
4. `resources/js/pages/vendor/Expenses.vue`
5. `resources/js/pages/vendor/Analytics.vue`

## Success Criteria
- All cards have proper dark backgrounds in dark mode
- All text is clearly visible in both modes
- Icon containers have subtle gold tint in dark mode
- Cards appear as separate components, not continuous backgrounds
- Consistent rounded corners (rounded-xl) throughout

## Technical Notes
- Use Tailwind's dark: prefix for conditional styling
- Avoid CSS overrides in favor of component-level changes
- Test both light and dark modes after each change
- Ensure proper contrast ratios for accessibility