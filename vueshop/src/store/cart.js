export default ({
  namespaced: true,
  state: {
    carts: []
  },
  mutations: {
    insert: (state, data) => {
      state.carts.push({
        id: data.id,
        title: data.title,
        cover: data.cover,
        price: data.price,
        weight: data.weight,
        quantity: 1
      })
    },
    update: (state, payload) => {
      // cek posisi ixdex dari payload
      let idx = state.carts.indexOf(payload)
      state.carts.splice(idx, 1, {
        id: payload.id,
        title: payload.title,
        cover: payload.cover,
        price: payload.price,
        weight: payload.weight,
        quantity: payload.quantity
      });

      if (payload.quantity <= 0) {
        state.carts.splice(idx, 1)
      }
    },
    // batch update carts
    set: (state, payload) => {
      state.carts = payload
    }
  },
  actions: {
    tambah: ({ state, commit }, payload) => {
      // cek data isPresent
      let cartItem = state.carts.find(item => item.id === payload.id)
      // jika tidak ada data maka mutasi insert dijalankan
      if (!cartItem) {
        commit('insert', payload)
      }
      else {
        cartItem.quantity++
        commit('update', cartItem)
      }
    },
    // menghapus cart pada item tertentu 
    remove: ({ state, commit }, payload) => {
      let cartItem = state.carts.find(item => item.id === payload.id)
      if (cartItem) {
        cartItem.quantity--
        commit('update', cartItem)
      }
    },
    // bacth update carts
    set: ({ commit }, payload) => {
      commit('set', payload)
    },
  },
  modules: {
  },
  getters: {
    carts: state => state.carts,
    count: (state) => {
      return state.carts.length
    },
    // menghitung total harga
    totalPrice: (state) => {
      let total = 0
      state.carts.forEach(function (cart) {
        total += cart.price * cart.quantity
      })
      return total
    },
    // total jumlah barang
    totalQuantity: (state) => {
      let total = 0
      state.carts.forEach(function (cart) {
        total += cart.quantity
      })
      return total
    },
    // total berat barang
    totalWeight: (state) => {
      let total = 0
      state.carts.forEach(function (cart) {
        total += cart.weight
      })
      return total
    }
  },

})