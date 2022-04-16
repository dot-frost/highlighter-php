<template>
    <div class="min-h-screen w-full p-3">
        <table class="table w-full">
            <!-- head -->
            <thead>
            <tr>
                <th>Text</th>
                <th>Meaning</th>
                <th>Options</th>
                <th>Examples</th>
                <th class="print:hidden">Exercise</th>
                <th class="print:hidden">Voices</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="phrase in phrases">
                <td>
                    <span>{{ phrase.phrase }}</span>
                </td>
                <td>
                    <div v-for="( mean, lang ) in phrase.information.meaning">
                        <span class="text-xs">{{ lang.toUpperCase() }}: </span>
                        <span>{{ mean }}</span>
                    </div>
                </td>
                <td>
                    <div v-for="option in phrase.information.options">
                        <span class="badge badge-info">{{ option.name }}: {{ option.value }}</span>
                    </div>
                </td>
                <th>
                    <div v-for="example in phrase.information.examples">
                        <span>{{ example.text }}</span>
                        <br>
                        <span class="badge badge-ghost badge-md">Meaning: {{ example.meaning.fa }}</span>
                        <div class="divider m-0"></div>
                    </div>
                </th>
                <th class="print:hidden">
                    <span class="badge badge-ghost badge-md">{{ phrase.information.exercise }}</span>
                </th>
                <td class="print:hidden">
                    <div v-for="voice in phrase.information.voices">
                        <span class="badge badge-info">{{ voice.name }}: {{ voice.value }}</span>
                    </div>
                </td>
            </tr>
            </tbody>
            <!-- foot -->
            <tfoot>
            </tfoot>

        </table>
    </div>
</template>

<script>
export default {
    name: "Extract",
    props:{
        phrases: Array,
        pages: Array,
    }
}
</script>

<style scoped>

</style>
