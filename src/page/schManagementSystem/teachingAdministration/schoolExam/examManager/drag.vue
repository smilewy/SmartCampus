<template>
  <div class="seatBody clearfix">
    <div class="seatRow" :data-col="ob.seatCol" :data-row="ob.seatRow" :data-num="ob.seatNumber" v-for="ob in dataList"
         :key="ob.name">
      <span class="seat" :class="{'active':ob.seatNumber}">{{ob.seatNumber}}</span>
    </div>
  </div>
</template>
<script>
  import '../../../../../../static/Tdrag/Tdrag'
  export default{
    props: ['dataList'],
    data(){
      return {}
    },
    methods: {},
    mounted: function () {
      $(".seatRow").Tdrag({
        scope: ".seatBody",
        pos: true,
        dragChange: true,
        axis: "all"
      });
      var lAry = $('.seatBody .seatRow'), len = lAry.length, posAry = [];
      for (let i = 0; i < len; i++) {
        let pos = {
          index: lAry.eq(i).attr("index"),
          left: lAry[i].style.left,
          top: lAry[i].style.top
        };
        posAry.push(pos);
      }
      this.$emit('getPos', posAry);
    }
  }
</script>
<style>
  .examinationDistribution .seatBody {
    padding: 0 30px;
    height: 28rem;
    overflow: auto;
    position: relative;
  }

  .examinationDistribution .seatBody .seatRow {
    float: left;
    width: 8%;
    margin-top: 15px;
    margin-left: 1%;
  }

  .examinationDistribution .seatBody .seat {
    display: block;
    border: 1px solid #89bcf5;
    border-radius: 6px;
    text-align: center;
    height: 30px;
    line-height: 30px;
    cursor: pointer;
  }

  .examinationDistribution .seatBody .seat.active {
    color: #fff;
    background-color: #89bcf5;
  }
</style>
