<template>
  <div class="w-full h-full bg-blue-300 grid grid-cols-4 gap-2 p-4" @dragover.prevent @drop.prevent="drop" v-if="false">
      <div v-for="image in images" :key="image.name" class="card image-full col-span-1 aspect-square">
          <figure><img :src="image.dataUrl" class=""></figure>
          <div class="card-body justify-between items-center">
              <input type="text" class="input input-sm input-bordered w-full text-base-content" v-model="image.number">
              <div class="card-actions btn-group">
                  <button class="btn btn-xs btn-info">
                      <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-xs btn-info">
                      <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-xs btn-info">
                      <i class="fas fa-edit"></i>
                  </button>
              </div>
          </div>
      </div>
  </div>
</template>

<script>
import Dashboard from "../Layouts/Dashboard";

export default {
    name: "Home",
    layout: Dashboard,
    data() {
        return {
            images: [],
            imageTemp: {
                dataUrl: null,
                name: null,
                number: null,
                size: null,
                type: null,
                isUploading: false,
                isUploaded: false
            },
        };
    },
    methods: {
        drop(e) {
            const files = e.dataTransfer.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (['image/jpeg', 'image/png'].includes(file.type) && file.lastModified < new Date().getTime()) {
                    const fileReader = new FileReader();
                    fileReader.onload = (e) => {
                        const image = {...this.imageTemp}
                        image.dataUrl = e.target.result;
                        image.name = file.name;
                        image.size = file.size;
                        image.type = file.type;
                        image.number = parseInt(file.name.split('.').shift());
                        this.images.push(image);
                    };
                    fileReader.readAsDataURL(file);
                }
            }
        }
    }
}
</script>
