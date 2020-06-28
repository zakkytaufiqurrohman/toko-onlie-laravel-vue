export default {
    namespaced: true,
    state : {
        provinces : [],
        cities: []
    },
    mutations: {
        setProvinces : (state, value) => {
            state.provinces = value
        },
        setCities: (state, value) => {
            state.cities = value
        }
    },
    actions: {
        setProvinces: ({commit}, payload ) => {
            commit('setProvinces', payload)
        },
        setCities: ({commit}, payload) => {
            commit('setCities', payload)
        }
    },
    getters: {
        provinces : state => state.provinces,
        cities: state => state.cities
    }
}