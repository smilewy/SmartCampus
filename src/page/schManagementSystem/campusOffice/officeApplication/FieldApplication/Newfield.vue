<template>
  <div class='Newfield'>
    <h3>新建场地申请</h3>
    <el-row class="Newfield-time" type="flex" align="center">
      <el-form :inline="true" class="formInline">
        <el-form-item label="选择日期：">
          <el-date-picker
            v-model="selectTime" type="date" :picker-options="pickerOptions0" style="width: 100%">
          </el-date-picker>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" style="margin-left: 1rem;" icon="el-icon-search" @click="queryField()">查询</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="alertsList">
      <el-table
        v-loading.body="isLoading"
        element-loading-text="拼命加载中..."
        :data="tableData"
        style="width: 100%">
        <el-table-column
          type="index"
          label="序号"
          align="center"
          width="76">
        </el-table-column>
        <el-table-column
          prop="buildingNumber"
          label="栋号"
          align="center">
        </el-table-column>
        <el-table-column
          prop="buildingName"
          label="楼栋名称"
          align="center">
        </el-table-column>
        <el-table-column
          prop="floor"
          label="层"
          align="center">
        </el-table-column>
        <el-table-column
          prop="room"
          label="号"
          align="center">
        </el-table-column>
        <el-table-column
          prop="name"
          label="场地"
          align="center">
        </el-table-column>
        <el-table-column
          prop="occupyTime"
          label="使用情况"
          align="center"
          width="460">
          <template  slot-scope="scope">
            <span>{{scope.row.occupyTime}}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="操作"
          align="center"
          width="100">
          <template  slot-scope="scope">
            <!--<span style="color:#4da1ff;cursor: pointer;" @click="Todatails(scope.row)">申请使用</span>-->
            <span style="color:#4da1ff;cursor: pointer;" @click="Todatails({name:'NewfieldDetails',params:{id:scope.row.id,name:scope.row.name}})">申请使用</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
  export default{
    data(){
      return{
        isLoading:false,
        selectTime:new Date(),
        id:'',
        tableData:[],
        pickerOptions0: {
          disabledDate(time) {
//                  return time.getTime() < Date.now() - 8.64e7;
          }
        },
      }
    },
    created(){
      this.queryField();
    },
    methods: {
      queryField(){
        this.isLoading=true;
        let selectTime= formatdata.format(this.selectTime,'yyyy-MM-dd HH:mm:ss');
        let param={
          date:selectTime
        };
        req.ajaxSend('/school/WorkDemand/createPlace','post',param,(res)=>{
          if (res.data === null) {
            this.tableData = [];
            this.isLoading=false;
            return;
          }
          res.data.forEach(val=>{
            if(val.occupyTime){
              val.occupyTime = val.occupyTime.map(subVal=>subVal).join('、');
            }
          });
          this.tableData =res.data;
          this.isLoading=false;
        });
      },
      Todatails(row){
//        sessionStorage.setItem('FileId',JSON.stringify({
//          id:row.id,
//          name:row.name
//        }));
//        this.$router.push({name:'NewfieldDetails',params:{id:row.id,name:row.name,occupyTime:row.occupyTime,todayTime:row.todayTime,}})
        this.$router.push(row)
      }
    }
  }
</script>
<style lang="less" scoped>
  .Newfield {
    .Newfield-time {
      padding: 2rem 0 0 0;
    }
  }
</style>
