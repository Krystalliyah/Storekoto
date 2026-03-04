# Reusable UI Components

## Table Components

### Basic Table Usage

```vue
<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const users = [
    { id: 1, name: 'John Doe', email: 'john@example.com', role: 'Admin' },
    { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'User' },
];
</script>

<template>
    <Table>
        <TableCaption>A list of your recent users.</TableCaption>
        <TableHeader>
            <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Email</TableHead>
                <TableHead>Role</TableHead>
                <TableHead class="text-right">Actions</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="user in users" :key="user.id">
                <TableCell class="font-medium">{{ user.name }}</TableCell>
                <TableCell>{{ user.email }}</TableCell>
                <TableCell>{{ user.role }}</TableCell>
                <TableCell class="text-right">
                    <Button variant="ghost" size="sm">Edit</Button>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
```

## Modal Components

### ConfirmationModal

Simple confirmation dialog for destructive or important actions.

```vue
<script setup lang="ts">
import { ref } from 'vue';
import { ConfirmationModal } from '@/components/ui/modal';

const showDeleteModal = ref(false);

const handleDelete = () => {
    // Perform delete action
    console.log('Item deleted');
    showDeleteModal.value = false;
};
</script>

<template>
    <Button @click="showDeleteModal = true">Delete Item</Button>

    <ConfirmationModal
        v-model:open="showDeleteModal"
        title="Delete Item"
        description="Are you sure you want to delete this item? This action cannot be undone."
        confirm-text="Delete"
        cancel-text="Cancel"
        variant="destructive"
        @confirm="handleDelete"
    />
</template>
```

### FormModal

Modal with form content and submit/cancel actions.

```vue
<script setup lang="ts">
import { ref } from 'vue';
import { FormModal } from '@/components/ui/modal';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const showFormModal = ref(false);
const formData = ref({ name: '', email: '' });

const handleSubmit = () => {
    // Submit form data
    console.log('Form submitted:', formData.value);
    showFormModal.value = false;
};
</script>

<template>
    <Button @click="showFormModal = true">Add User</Button>

    <FormModal
        v-model:open="showFormModal"
        title="Add New User"
        description="Enter the user details below."
        submit-text="Create User"
        max-width="lg"
        @submit="handleSubmit"
    >
        <div class="grid gap-4">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" v-model="formData.name" placeholder="John Doe" />
            </div>
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input id="email" type="email" v-model="formData.email" placeholder="john@example.com" />
            </div>
        </div>
    </FormModal>
</template>
```

## Props

### ConfirmationModal Props

- `open` (boolean, required): Controls modal visibility
- `title` (string, required): Modal title
- `description` (string, required): Modal description
- `confirmText` (string, optional): Confirm button text (default: "Confirm")
- `cancelText` (string, optional): Cancel button text (default: "Cancel")
- `variant` ('default' | 'destructive', optional): Button variant (default: "default")
- `loading` (boolean, optional): Disable buttons during loading (default: false)

### FormModal Props

- `open` (boolean, required): Controls modal visibility
- `title` (string, required): Modal title
- `description` (string, optional): Modal description
- `submitText` (string, optional): Submit button text (default: "Submit")
- `cancelText` (string, optional): Cancel button text (default: "Cancel")
- `loading` (boolean, optional): Disable buttons during loading (default: false)
- `maxWidth` ('sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl', optional): Modal max width (default: "md")

## Events

### ConfirmationModal Events

- `@confirm`: Emitted when confirm button is clicked
- `@cancel`: Emitted when cancel button is clicked
- `@update:open`: Emitted when modal visibility changes

### FormModal Events

- `@submit`: Emitted when submit button is clicked
- `@cancel`: Emitted when cancel button is clicked
- `@update:open`: Emitted when modal visibility changes
