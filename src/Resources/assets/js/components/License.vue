<template>
  <div>
    <div class="modal fade" id="modal-llc" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modal-llcLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form action="" method="POST" @submit.prevent="buy">
          <div class="modal-content">
            <div class="modal-header border-0">
              <h5 class="modal-title" id="modal-llcLabel">Buy License $5</h5>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" class="form-control" v-model="body.name" required placeholder="Your name">
              </div>

              <div class="form-group">
                <label for="email">E-Mail Address</label>
                <input type="email" id="email" class="form-control" v-model="body.email" required placeholder="Your Email">
              </div>

              <div class="form-group">
                <label for="domain">Your Domain App</label>
                <input type="text" id="domain" class="form-control" v-model="body.domain" required placeholder="domain.com">
                <small class="text-muted"></small>
              </div>
            </div>
            <div class="modal-footer border-0">
              <button type="submit" class="btn btn-primary">Buy Now &rarr;</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
<script>
import axios from 'axios';
export default {
  data() {
    return {
      body: {}
    }
  },
  mounted() {
    this.getEnv();
    this.body = {
      ...this.body,
      domain: this.getDomain()
    }
  },
  methods: {
    getDomain() {
      let domain = window.location.hostname;
      let full = window.location.href;
      let split = full.split('//');
      let protocol = split[0];

      return `${protocol}//${domain}`;
    },
    getEnv() {
      axios.post('/administrator/coinpayment/ajax/environment')
        .then(json => {
          if(json.data.result) {
            $('#modal-llc').modal('show');
          }
        })
    },
    buy() {
      console.log(this.body);
    }
  }
}
</script>