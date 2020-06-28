

<template>
  <div>
    <v-card :to="'/category/'+ category.slug" v-if="category.slug">
      <v-img :src="getImage('/categories/'+category.image)" class="white--text">
        <v-card-title class="fill-height align-end" v-text="category.name"></v-card-title>
      </v-img>
    </v-card>
    <v-container class="ma-0 pa-0" grid-list-sm v-if="books">
      <v-subheader>All Books</v-subheader>
      <v-layout wrap>
        <v-flex v-for="(book) in books" :key="`book-`+book.id" xs6>
          <v-card :to="'/book/'+ book.slug">
            <v-img :src="getImage('/books/'+book.cover)" class="white--text">
              <v-card-title class="fill-height align-end" v-text="book.title"></v-card-title>
            </v-img>
          </v-card>
        </v-flex>
      </v-layout>
    </v-container>
    <template>
      <div class="text-center">
        <v-pagination v-model="page" @input="go" :length="lengthPage" :total-visible="5"></v-pagination>
      </div>
    </template>
  </div>
</template>
<script>
export default {
  data: () => ({
    category: {},
    books: [],
    page: 0,
    lengthPage: 0
  }),
  created() {
    this.go();
  },
  methods: {
    go() {
      let { slug } = this.$route.params;
      let url = "/categories/detail/" + slug;
      url = url + "?page" + this.page;
      url = encodeURI(url);

      // get
      this.axios.get(url).then(response => {
          console.log('sda',response)
        let { data } = response.data;
        let cate = data;
        this.category = data;
        this.books = cate.books.data;
        this.page = cate.books.current_page;
        this.lengthPage = cate.books.last_page;
      });
    }
  }
};
</script>