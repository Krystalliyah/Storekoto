<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import VendorLayout from '@/layouts/VendorLayout.vue';
import { ref, onBeforeUnmount } from 'vue';

const props = defineProps<{
    products: {
        data: Array<{
            id: number;
            name: string;
            description: string | null;
            price: number;
            stock: number;
            created_at: string;
            image_path?: string | null;
        }>;
    };
}>();

const showModal = ref(false);
const editingProduct = ref<any>(null);

const form = useForm({
    name: '',
    description: '',
    price: 0,
    stock: 0,
    image: null as File | null,
    _method: null as null | 'put',
});

const imagePreviewUrl = ref<string | null>(null);
const imageInputRef = ref<HTMLInputElement | null>(null);

function onImageChange(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    // reset
    form.image = null;
    if (imagePreviewUrl.value) URL.revokeObjectURL(imagePreviewUrl.value);
    imagePreviewUrl.value = null;

    if (!file) return;

    // basic client-side guard (optional)
    if (!file.type.startsWith('image/')) return;

    form.image = file;
    imagePreviewUrl.value = URL.createObjectURL(file);
}

function removeSelectedImage() {
    form.image = null;

    if (imagePreviewUrl.value) URL.revokeObjectURL(imagePreviewUrl.value);
    imagePreviewUrl.value = null;

    // clear file input
    if (imageInputRef.value) imageInputRef.value.value = '';
}

function openCreateModal() {
    editingProduct.value = null;
    form.reset();
    removeSelectedImage();
    showModal.value = true;
}

function openEditModal(product: any) {
    editingProduct.value = product;
    form.name = product.name;
    form.description = product.description || '';
    form.price = product.price;
    form.stock = product.stock;

    // clear any previously selected local file preview
    removeSelectedImage();

    // show existing image from DB (if present)
    if (product.image_path) {
        imagePreviewUrl.value = `/storage/${product.image_path}`;
    }

    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
    editingProduct.value = null;

    // clear preview
    removeSelectedImage();
}

function submit() {
  if (editingProduct.value) {
    form._method = 'put';
    form.post(`/vendor/products/${editingProduct.value.id}`, {
      forceFormData: true,
      onSuccess: () => closeModal(),
      onFinish: () => (form._method = null),
    });
  } else {
    form.post('/vendor/products', {
      forceFormData: true,
      onSuccess: () => closeModal(),
    });
  }
}

function deleteProduct(id: number) {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(`/vendor/products/${id}`);
    }
}

onBeforeUnmount(() => {
    if (imagePreviewUrl.value?.startsWith('blob:')) {
        URL.revokeObjectURL(imagePreviewUrl.value);
    }
});

</script>

<template>
    <Head title="Products" />

    <VendorLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Products</h1>
                <button 
                    @click="openCreateModal" 
                    class="px-4 py-2 bg-emerald-700 text-white rounded-lg hover:bg-emerald-800"
                >
                    + Add Product
                </button>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="product in products.data" :key="product.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img v-if="product.image_path" :src="`/storage/${product.image_path}`" class="h-12 w-12 object-cover rounded">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ product.name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ product.description || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱{{ product.price }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ product.stock }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button @click="openEditModal(product)" class="text-blue-600 hover:text-blue-900">
                                    Edit
                                </button>
                                <button @click="deleteProduct(product.id)" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="products.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No products yet. Click "Add Product" to create one.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal -->
            <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <h2 class="text-xl font-bold mb-4 text-gray-900">
                        {{ editingProduct ? 'Edit Product' : 'Add Product' }}
                    </h2>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Product Name
                            </label>
                            <input 
                                id="name" 
                                v-model="form.name" 
                                required 
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea 
                                id="description" 
                                v-model="form.description" 
                                rows="3"
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            ></textarea>
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                Price (₱)
                            </label>
                            <input 
                                id="price" 
                                v-model="form.price" 
                                type="number" 
                                step="0.01" 
                                required 
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>

                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">
                                Stock
                            </label>
                            <input 
                                id="stock" 
                                v-model="form.stock" 
                                type="number" 
                                required 
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>

                        <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Product Image
                        </label>

                        <div class="flex items-start gap-4">
                        <!-- Preview Box -->
                        <div class="w-28 h-28 rounded-lg border border-gray-200 bg-gray-50 overflow-hidden flex items-center justify-center">
                        <img
                            v-if="imagePreviewUrl"
                            :src="imagePreviewUrl"
                            class="w-full h-full object-cover"
                            alt="Preview"
                        />
                        <div v-else class="text-xs text-gray-400 text-center px-2">
                            No image
                        </div>
                    </div>

        <!-- Controls -->
        <div class="flex-1 space-y-2">
            <input
                ref="imageInputRef"
                type="file"
                accept="image/*"
                @change="onImageChange"
                class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4
                       file:rounded-md file:border-0 file:text-sm file:font-semibold
                       file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
            />

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="removeSelectedImage"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Remove
                </button>

                <p class="text-xs text-gray-500">
                    JPG/PNG/WEBP up to 2MB.
                </p>
            </div>

            <p v-if="form.errors.image" class="text-xs text-red-600">
                {{ form.errors.image }}
            </p>
        </div>
    </div>
</div>

                        <div class="flex justify-end space-x-2 pt-4">
                            <button 
                                type="button" 
                                @click="closeModal" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 disabled:opacity-50"
                            >
                                {{ editingProduct ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </VendorLayout>
</template>
