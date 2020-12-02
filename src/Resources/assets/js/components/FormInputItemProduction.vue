<template>
  <ladmin-layout>
    
    <div class="table-responsive">
      <table class="table mb-3">
        <thead>
          <tr>
            <th class="border-0 pl-0">Description Product *</th>
            <th class="border-0 pl-0">Price *</th>
            <th class="border-0 pl-0">Qty *</th>
            <th class="border-0 pl-0">Subtotal</th>
            <th class="border-0 pl-0"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in inputForms" :key="i" class="border-0">
            <td class="border-0 pl-0" width="50%">
              <input type="text" name="itemDescription[]" v-model="item.itemDescription" class="form-control" required placeholder="Description product...">
            </td>
            <td class="border-0 pl-0" width="15%">
              <input type="number" step="0.01" name="itemPrice[]" @change="calculation(item)" v-model="item.itemPrice" class="form-control text-right" required :placeholder="currency + ' Price'">
            </td>
            <td class="border-0 pl-0" width="15%">
              <input type="number" step="1" name="itemQty[]" @change="calculation(item)" v-model="item.itemQty" class="form-control text-center" required placeholder="Qty">
            </td>
            <td class="border-0 pl-0" width="15%">
              <input type="number" step="0.01" name="itemSubtotalAmount[]" readonly v-model="item.itemSubtotalAmount" class="form-control text-right" required placeholder="Subtotal">
            </td>
            <td class="border-0 pl-0" width="5%">
              <button @click="removeRow(item, i)" :disabled="i == 0" class="btn btn-danger">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th class="text-left border-0" colspan="2">
              <button type="button" @click="addNewRow" class="btn btn-outline-primary">&plus; Add new row</button>
              <button type="submit" class="btn btn-primary">Submit Transaction &rarr;</button>

              <h5 class="float-right">Total Amount</h5>
            </th>
            <th colspan="2" class="text-right border-0">
              <h5>{{ total }} {{ currency }}</h5>
            </th>
            <th class="border-0"></th>
          </tr>
        </tfoot>
      </table>
    </div>
    
  </ladmin-layout>
</template>
<script>
import LadminLayout from './LadminLayout';
export default {
  props: ['currency'],
  components: {
    LadminLayout
  },
  data() {
    return {
      total: 0,
      inputForms: [
        {
          itemDescription: '',
          itemPrice: 0,
          itemQty: 1,
          itemSubtotalAmount: 0,
        }
      ]
    };
  },
  methods: {
    addNewRow() {
      this.inputForms.push({
          itemDescription: '',
          itemPrice: 0,
          itemQty: 1,
          itemSubtotalAmount: 0,
        });
    },
    removeRow(item, i) {
      if(i === 0) {
        return;
      }
      this.inputForms.splice(i, 1);
    },
    calculation(item) {
      let subtotal = parseFloat(item.itemPrice) * parseInt(item.itemQty);
      item.itemSubtotalAmount = subtotal;

      let total = 0;
      for(item of this.inputForms) {
        total += parseFloat(item.itemSubtotalAmount);
      }
      
      this.total = total.toFixed(2);
    }
  }
}
</script>