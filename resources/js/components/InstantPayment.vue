<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col mt-5">
                <div class="card-body"><h5><strong class="header-text">Instant Payment</strong></h5></div>
                <form @submit.prevent="submitForm()" method="POST" ref="instantPaymentForm">
                    <div class="form-group pt-4 pb-4 bg-warning">
                        <input type="hidden" name="amount" class="form-control" :value="amount">
                        <div class="text-center pt-2">
                            <button type="submit" name="get_payment" class="btn btn-primary">Payment</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
             url: {},
             orderId: {},
             amount: 1000,
        }
    },
    methods: {
         instantPayment(formData) {
             axios.post('/blue-media/init', formData).then(response => {
                 this.url = response.data.url;
                 this.orderId = response.data.orderId;
                 window.location.href = this.url;
             }).catch(function (error) {

             });
         },
        submitForm() {
            let form = this.$refs.instantPaymentForm;
            let formData = new FormData(form);
            formData.append('amount', this.amount);
            this.instantPayment(formData);
        },
    },
}
</script>

<style scoped>

    .header-text {
        color: #8f8f8f;
    }

</style>
