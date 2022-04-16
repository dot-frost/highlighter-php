<template>
    <div class="container flex flex-col justify-start items-center min-h-screen py-2 gap-4">
        <div class="flex justify-between w-full">
            <h1 class="text-4xl font-bold">
                Books
            </h1>
            <div class="flex justify-evenly">
                <Link class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Add Book
                </Link>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-2">
            <div class="card bg-base-100 shadow-xl image-full card-bordered border-gray-300 bg-base-100 shadow-xl" v-for="book in books" :key="`book-${book.id}`">
                <figure><img :src="book.coverUrl"  :alt="book.title"/></figure>
                <div class="card-body justify-between items-center">
                    <h2 class="card-title">{{ book.title }}</h2>
                    <div class="card-actions justify-center flex-nowrap justify-evenly">
                        <Link v-if="$store.auth.can('books.read', book.id)" :href="$route('books.pages.index', book.id)" class="btn btn-primary">
                            <i class="fas fa-book-open"></i>
                        </Link>
                        <Link v-if="$store.auth.can('books.destroy', book.id)" as="button" :href="$route('books.destroy', book.id)" method="delete" class="btn btn-error">
                            <i class="fas fa-trash-alt"></i>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Dashboard from "../../Layouts/Dashboard";
import {useForm} from "@inertiajs/inertia-vue3";
import {ref} from "vue";
import { Link } from "@inertiajs/inertia-vue3";

export default {
    setup() {
        const createForm = useForm({
            title: '',
            coverUrl: '',
        });
        return {
            form: createForm,
            createBook(){
                createForm.post(route('books.store'));
            },
            isOpenCreateModal: ref(false),
        };
    },
    name: "Index",
    layout: Dashboard,
    props:{
        books: Array
    },
    components: {
        Link,
    }
}
</script>

<style scoped>

</style>
