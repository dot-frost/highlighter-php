<template>
    <div class="flex justify-center items-center min-h-screen p-4 bg-gray-200"
         style="background-image: url('https://images.freeimages.com/images/large-previews/e97/open-up-your-mind-3-1573027.jpg'); background-size: cover; background-position: center;">
        <div class="card bg-white w-10/12 sm:w-8/12 md:w-6/12 lg:w-4/12 ">
            <div class="p-5 md:flex-1">
                <slot/>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Auth",
}
</script>

<style scoped>

</style>
