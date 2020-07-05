<template>
  <v-card>
    <v-subheader>History Belanja</v-subheader>
    <div>
      <v-container>
        <v-simple-table>
          <tbody>
            <tr v-for="item in items" :key="item.id">
              <td>
                Invoice: {{item.invoice_number}}
                <div class="primary--text title">
                  Rp. {{item.total_price.toLocaleString('id-ID')}}
                </div>
                <small>
                  date: {{ item.updated_at }}. courier: {{item.courier_service }}
                </small>
              </td>
              <td>Status : {{item.status}}</td>
            </tr>
          </tbody>
        </v-simple-table>
      </v-container>
    </div>
  </v-card>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
export default {
  data() {
    return {
      items: []
    };
  },
  computed: {
    ...mapGetters({
      user: "auth/user"
    })
  },
  methods: {
    ...mapActions({
      setAlert: "alert/actionSet"
    })
  },
  mounted() {
    let config = {
      headers: {
        Authorization: "Bearer " + this.user.api_token
      }
    };
    this.axios.get("myOrder", config).then(response => {
      let { data } = response.data;
      this.items = data;
    });
  }
};
</script>