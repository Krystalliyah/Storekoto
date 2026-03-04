<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface Props {
    open: boolean;
    title: string;
    description?: string;
    submitText?: string;
    cancelText?: string;
    loading?: boolean;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl';
}

withDefaults(defineProps<Props>(), {
    submitText: 'Submit',
    cancelText: 'Cancel',
    loading: false,
    maxWidth: 'md',
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    submit: [];
    cancel: [];
}>();

const maxWidthClasses = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    '3xl': 'sm:max-w-3xl',
};

const handleSubmit = () => {
    emit('submit');
};

const handleCancel = () => {
    emit('cancel');
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent :class="maxWidthClasses[maxWidth]">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription v-if="description">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>
            
            <div class="space-y-4">
                <slot />
            </div>

            <DialogFooter class="gap-2">
                <Button variant="outline" @click="handleCancel" :disabled="loading">
                    {{ cancelText }}
                </Button>
                <Button @click="handleSubmit" :disabled="loading">
                    {{ submitText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
