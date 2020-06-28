
<template>
  <div>
    <v-card :to="'/details/'+ buku.slug">
      <v-img :src="getImage('/book/'+buku.cover)" class="white--text" height="200px">
        <v-card-title class="fill-height align-end" v-text="buku.title"></v-card-title>
      </v-img>
      <v-card-actions>
        <v-icon>mdi-cash</v-icon>
        <span class="orange--text">
          Rp {{ buku.price.toLocaleString('id-ID') }}
        </span>
        <v-spacer></v-spacer>
        <v-icon>mdi-eye</v-icon>
        {{ buku.viewer }}
      </v-card-actions>
    </v-card>
  </div>
</template>


<script>
export default {
  name: "book-item",
  props: ['buku'],
  data: () => ({})
};
</script>