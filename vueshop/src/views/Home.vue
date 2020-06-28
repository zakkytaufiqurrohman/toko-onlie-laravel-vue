<template>
  <div>
    <!-- template categories -->
    <v-container class="ma-0 pa-0" grid-list-sm>
      <div class="text-right">
        <v-btn small text to="categories" class="blue--text">
          All-categories
          <v-icon>mdi-chevron-right</v-icon>
        </v-btn>
      </div>

      <v-layout wrap>
        <v-flex v-for="(category) in categories" :key="`category`+category.id" xs6>
          <v-card :to="'/detail/'+ category.slug">
            <v-img :src="getImage('categories/' + category.image)" class="red--text">
              <v-card-title class="fill-height align-end" v-text="category.name"></v-card-title>
            </v-img>
          </v-card>
        </v-flex>
      </v-layout>
    </v-container>
    <!-- begin all book -->
    <v-container class="ma-0 pa-0 mt-2" grid-list-sm>
      <div class="text-right">
        <v-btn small text to="/books" class="blue--text">
          All Books
          <v-icon>mdi-chevron-right</v-icon>
        </v-btn>
      </div>
      <v-layout wrap>
        <v-flex v-for="(book) in books" :key="`book-`+book.id" xs6>
          <!-- <v-card :to="'/details/'+ book.slug">
            <v-img :src="getImage('book/'+ book.cover)" class="white--text">
              <v-card-title class="fill-height align-end" v-text="book.title"></v-card-title>
            </v-img>
          </v-card> -->
          <book-item :buku="book" />
        </v-flex>
      </v-layout>
    </v-container>
  </div>
</template>

<script>
// @ is an alias to /src
// import HelloWorld from '@/components/HelloWorld.vue'

export default {
  name: "Home",
  data: () => ({
    categories: [],
    books: []
  }),
  created() {
    this.axios
      .get("categories/random/2")
      .then(response => {
        let { data } = response.data;
        this.categories = data;
      })
      .catch(error => {
        let { response } = error;
        console.log(response);
      });
    // get book from api
    this.axios
      .get("book/top/4")
      .then(response => {
        let { data } = response.data;
        this.books = data;
      })
  },
  components: {
    BookItem: () => import('@/components/BookItem.vue')
  }
};
</script>
