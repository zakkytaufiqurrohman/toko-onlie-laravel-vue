<template>
    <div>
        <v-subheader>Shipping Address</v-subheader>
        <div>
            <v-card flat>
                <v-container>
                    <v-form lazy-validation>
                        <v-text-field
                            v-model="name"
                            label="name"
                            required
                            append-icon="mdi-user"
                        >
                        </v-text-field>
                        <v-textarea
                            label="Address"
                            v-model="address"
                            required
                            auto-grow
                            rows=3
                        >
                        </v-textarea>

                        <v-text-field
                            label="phone"
                            v-model="phone"
                            required
                            append-icon="mdi-phone"
                        >
                        </v-text-field>

                        <v-select
                            v-model="province_id"
                            label="province"
                            :items = "provinces"
                            item-text="province"
                            item-value="id"
                            persistent-hint
                            single-line
                            required
                        >
                        </v-select>

                        <v-select
                            label="city"
                            v-model="city_id"
                            v-if="province_id > 0"
                            :items="citiesByProvince"
                            item-text = "city"
                            item-value = "id"
                            persistent-hint
                            single-line
                        >
                        </v-select>
                    </v-form>
                    <v-card-actions>
                        <v-btn color="success" dark @click="saveShipping">
                            <v-icon>mdi-content-save</v-icon> &nbsp;
                        </v-btn>
                    </v-card-actions>
                    <!-- menampilkah hasil belanja -->
                    <v-subheader>Your shopping cart </v-subheader>
                    <div v-if="countCart > 0" >
                        <v-card flat>
                            <v-list three-line v-if="countCart>0" >
                                <template v-for="(item, index) in carts" >
                                    <v-list-item :key="'cart'+index">
                                        <v-list-item-avatar>
                                            <v-img :src="getImage('/books/'+item.cover)"></v-img>
                                        </v-list-item-avatar>

                                        <v-list-item-content>
                                            <v-list-item-title v-html="item.title"></v-list-item-title>
                                            <v-list-item-subtitle>
                                                Rp.{{item.price.toLocaleString('id-ID')}}
                                                ({{item.weight}} kg)
                                                ({{totalWeight}})
                                                <span style="float:rigth">
                                                    {{item.quantity}}
                                                </span>
                                            </v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                </template>
                            </v-list>
                            <!-- menampilkan subtotal -->
                            <v-container>
                                <v-card-actions>
                                    subtotal
                                    <v-spacer/>
                                    Rp.{{totalPrice.toLocaleString('id-ID')}}
                                </v-card-actions>
                            </v-container>
                            <!-- select courier -->
                            <v-subheader>Courier</v-subheader>
                            <div>
                                <v-card flat>
                                    <v-container>
                                        <v-select
                                            v-model="courier"
                                            :items="couriers"
                                            @change="getServices"
                                            item-text="text"
                                            item-value="id"
                                            label = "courier"
                                            persistent-hint
                                            single-line
                                        >
                                        </v-select>
                                        <v-select
                                            v-model="service"
                                            v-if="courier"
                                            :items = "services"
                                            @change="calculateBil"
                                            item-text="resume"
                                            item-value="service"
                                            label="courier Service"
                                            persistent-hint
                                            single-line
                                        >
                                        </v-select>
                                        <!-- subtotal ekspedisi -->
                                        <v-card-actions>
                                            subtotal
                                            <v-spacer/>
                                            Rp.{{shippingCost.toLocaleString('id-ID')}} 
                                        </v-card-actions>
                                    </v-container>
                                </v-card>
                            </div>
                            <!-- total -->
                            <v-subheader>Total</v-subheader>
                                <v-card>
                                    <v-container>
                                        <v-layout row wrap>
                                            <v-flex xs6 text-center>
                                                Total Bill ({{ totalQuantity }} items)
                                                <div class="title">
                                                    {{ totalBill.toLocaleString('id-ID')}}
                                                </div>
                                            </v-flex>
                                            <v-flex xs6 text-center>
                                                <v-btn color="orange">
                                                    <v-icon light>mdi-cash</v-icon>
                                                </v-btn>
                                            </v-flex>
                                        </v-layout>
                                    </v-container>
                                </v-card>
                        </v-card>
                    </div>
                </v-container>
            </v-card>
        </div>
    </div>
</template>
<script>
import { mapActions , mapGetters} from 'vuex'
export default {
    data () {
        return {
            'name': '',
            'address': '',
            'phone' : '',
            'province_id' : '',
            'city_id': '',
            courier : '',
            couriers : [],
            service : '',
            services : [],
            shippingCost : 0,
            totalBill : 0,
        }
    },
    computed : {
        ...mapGetters ({
            user : 'auth/user',
            provinces: 'region/provinces',
            cities : 'region/cities',
            countCart: 'cart/count',
            carts: 'cart/carts',
            totalPrice: 'cart/totalPrice',
            totalWeight: 'cart/totalWeight',
            totalQuantity: 'cart/totalQuantity'
        }),
        citiesByProvince() {
            let province_id = this.province_id
            return this.cities.filter((city) => {
                if( city.province_id == province_id ) return city;
            })
        }
    },
    methods : {
        ...mapActions({
            setAuth : 'auth/actionSet',//todo buat apa ?
            setAlert: 'alert/actionSet',
            setProvinces : 'region/setProvinces',
            setCities: 'region/setCities', //ini di panggil di jika kosong dan untuk set data region city
            setCart : 'cart/set'
        }),
        saveShipping() {
            let formData = new FormData()
            formData.set('name', this.name)
            formData.set('address', this.address)
            formData.set('phone', this.phone)
            formData.set('province_id', this.province_id)
            formData.set('city_id', this.city_id)

            let config = {
                headers: {
                    'Authorization' : 'Bearer ' + this.user.api_token,//after bearer dont forget space
                },
            }

            this.axios.post('/shipping', formData, config).then((response) => {
                let { data } = response
                this.setAuth(data.data)
                this.setAlert({
                    status: true,
                    text: data.message,
                    color : 'success'
                })
            }).catch((error) => {
                let { data } = error
                this.setAlert({
                    status: true,
                    text : data.message,
                    color: 'error'
                })
            })
        },
        getServices() {
            let courier = this.courier
            let endcodeCart = JSON.stringify(this.carts)
            console.log(endcodeCart);
            let formData = new FormData()
            formData.set('courier', courier)
            formData.set('carts', endcodeCart)

            let config = {
                headers : {
                    'Authorization' : 'Bearer ' + this.user.api_token
                }
            }
            
            this.axios.post('/services', formData, config).then( (response) => {
                let response_data = response.data
                // jika tidak error maka data sercice dan cart akan diupdate
                if (response_data.status  != 'error') {
                    this.services = response_data.data.services
                    // console.log(this.services);
                    this.setCart(response_data.data.safe_carts)
                }
                this.setAlert({
                    status: true,
                    text: response_data.message,
                    color: response_data.status
                })
            }).catch( (error) => {
                // let responses = error.data
                console.log(error);
                this.setAlert({
                    status : 'error',
                    text: 'error',
                    color: 'error',
                })
            })
        },
        calculateBil () {
            let selectedService = this.services.find((service) => {
                return (service.service == this.service)
            })
            this.shippingCost = selectedService.cost
            this.totalBill = parseInt(this.totalPrice) + parseInt(this.shippingCost)
        }
    },
    created() {
        this.name = this.user.name,
        this.address = this.user.address,
        this.phone = this.user.phone,
        this.city_id = this.user.city_id,
        this.province_id = this.user.province_id

        if(this.provinces  && this.provinces.length == 0) {
            this.axios.get('/province').then((response) => {
                let { data } = response.data
                this.setProvinces(data)
            })
            this.axios.get('/city').then((response) => {
                let { data } = response.data
                this.setCities(data)
            })
        }
        if (this.couriers.length == 0) {
            this.axios.get('/couriers').then((respon) => {
                this.couriers = respon.data.data
            })
        }
    }
}
</script>