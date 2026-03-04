<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/login';

// we don't need all the branding from the full login page; keep it simple
const isSubmitting = ref(false);
const onBeforeSubmit = () => {
    if (!isSubmitting.value) {
        isSubmitting.value = true;
        window.history.replaceState({ isLogin: true }, document.title, window.location.href);
    }
};

onMounted(() => {
    const form = document.querySelector('.it-form') as HTMLFormElement;
    if (form) {
        form.addEventListener('submit', onBeforeSubmit);
    }
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Tenant Admin Login" />

    <AuthBase>
        <div class="it-form-header">
            <h1 class="it-form-title">Store Administrator Login</h1>
            <p class="it-form-desc">Use your admin credentials to enter the vendor dashboard.</p>
        </div>

        <div v-if="status" class="it-status-msg">
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            :replace="true"
            v-slot="{ errors, processing }"
            class="it-form"
        >
            <!-- Login ID -->
            <div class="it-field">
                <Label for="login_id" class="it-label">Login ID</Label>
                <div class="it-input-wrap">
                    <Input
                        id="login_id"
                        type="text"
                        name="login_id"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin-tenantid"
                        class="it-input"
                    />
                </div>
                <InputError :message="errors.login_id" class="it-field-error" />
            </div>

            <!-- Password -->
            <div class="it-field">
                <Label for="password" class="it-label">Password</Label>
                <div class="it-input-wrap">
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="it-input"
                    />
                </div>
                <InputError :message="errors.password" class="it-field-error" />
            </div>

            <div class="it-field">
                <button type="submit" class="it-btn" :disabled="processing">Log in</button>
            </div>
        </Form>
    </AuthBase>
</template>
