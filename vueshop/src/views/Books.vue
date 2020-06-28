<template>
    <div>
        <v-container class="ma-0 pa-0" grid-list-sm>
            <v-subheader>
                All BOOKS
            </v-subheader>
            <v-layout wrap>
                <v-flex v-for="(book) in books" :key="`book-`+book.id" xs6>
                    <v-card :to="'/book'+ book.slug">
                        <v-img :src="getImage('/book/'+book.cover)" class="white--text">
                        </v-img>
                        <v-card-title class="fill-height align-end" v-text="book.title">
                        </v-card-title>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-container>
    <!-- pagination -->
    <template>
        <div class="text-center">
            <v-pagination
                v-model="page"
                @input="go"
                :length="lengthPage"
                :total-visible="5"
            >
            </v-pagination>
        </div>
    </template>
    </div>
</template>
<script>
export default {
    data: () => ({
        books: [],
        page: 0,
        lengthPage: 0,
    }),
    created() {
        this.go()
    },
    methods: {
        go() {
          let url = "book?page="+this.page
          this.axios.get(url).then((response) => {
              let { data } = response.data
              let { meta } = response.data

              this.books = data
              this.page = meta.current_page
              this.lengthPage = meta.last_page
          })
      }
    }
}
</script>