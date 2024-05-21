<template>
    <LoadingView :loading="false" :key="pageId">
        <div class="flex justify-content-start gap-4">
            <div class="w-1/5">
                <ul>
                    <li
                        v-for="(menu, index) in menus"
                        :key="index"
                        class="mb-2"
                    >
                        <menu-section :item="menu" />
                    </li>
                </ul>
            </div>
            <div class="w-full">
                {{ formData }}
                <form @submit.prevent="save" data-form-unique-id="adv">
                    <component
                        :is="`form-` + panelMapped.component"
                        :panel="panelMapped"
                        mode="form"
                        class="mb-6"
                        :resource-name="'nova-advance-settings'"
                        :resource-id="pageId"
                        :fields="panelMapped.fields"
                        :validation-errors="validationErrors"
                    />

                    <default-button>Save</default-button>
                </form>
            </div>
        </div>
    </LoadingView>
</template>

<script>
import { Errors, HandlesFormRequest, HandlesUploads } from "laravel-nova";

export default {
    mixins: [HandlesFormRequest, HandlesUploads],
    props: ["panel", "fields", "menus", "section"],
    data() {
        return {
            pageId: this.section,
            validationErrors: new Errors(),
            formFields: this.fields,
        };
    },
    methods: {
        async save() {
            const formData = new FormData();
            this.formFields.forEach((field) => field.fill(formData));

            try {
                let response = await Nova.request().post(
                    `${this.section}`,
                    formData
                );

                if (response.status == 204) {
                    Nova.success("Settings updated!");
                    this.validationErrors = new Errors();
                }
            } catch (error) {
                console.log(error.response.status);

                if (error && error.response && error.response.status == 422) {
                    this.validationErrors = new Errors(
                        error.response.data.errors
                    );
                    Nova.error("There was a problem submitting the form.");
                }
            }
        },
    },

    computed: {
        panelMapped() {
            return {
                name: this.panel.name,
                component: this.panel.component,
                helpText: this.panel.helpText,
                fields: this.formFields,
                showTitle: this.panel.showTitle,
            };
        },
    },
};
</script>
