<template>
  <div class="viewAlerts">
    <h3>发布记录</h3>
    <el-row class="alertsBtn" style="margin:2.25rem 0 1.25rem 0">
        <el-button class="delete" @click="deleteAlerts">
          <img class="delete_unactive" src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete.png" alt="">
          <img class="delete_active" src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete_highlight.png" alt="">
        </el-button>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        @sort-change="sort"
        @selection-change="handleSelectionChange"
        v-loading.body="isLoading"
        element-loading-text="拼命加载中...">
        <el-table-column
          type="selection"
          width="55"
          @change="chooseAll">
        </el-table-column>
        <el-table-column
          prop="title"
          label="标题"
          sortable="custom"
          align="center">
          <template  slot-scope="scope">
            <div>{{scope.row.title}}</div>
          </template>
        </el-table-column>
        <el-table-column
          prop="kind"
          label="类型"
          sortable="custom"
          align="center">
          <template  slot-scope="scope">
            <span v-if="scope.row.kind==1">通知</span>
            <span v-if="scope.row.kind==2">通告</span>
            <span v-if="scope.row.kind==3">通报</span>
            <span v-if="scope.row.kind==4">决议</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="draft"
          label="发布状态"
          sortable="custom"
          align="center">
          <template  slot-scope="scope">
            <span v-if="scope.row.draft==0">已发布</span>
            <span v-if="scope.row.draft==1">未发布</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="ratio"
          label="查阅进度"
          align="center">
          <template  slot-scope="scope">
            <span v-if="scope.row.draft==0">
              <span>{{scope.row.ratio}}</span>
              <span class="pu-speed" v-if="scope.row.state" @click="showDialogTable(scope.row)">[未查阅名单]</span>
            </span>
          </template>
        </el-table-column>
        <el-table-column
          prop="createTime"
          label="发布时间"
          sortable="custom"
          align="center">
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog title="未查阅名单" :visible.sync="dialogTableVisible" :modal="false" class="pu-dialog">
      <el-row>
        <el-col :span="24" style="height: 30rem;overflow-y: auto">
          <el-col :span="19">
            <el-radio-group v-model="radio" @change="changeData">
              <el-radio :label="14">教师</el-radio>
              <el-radio :label="15">学生</el-radio>
              <el-radio :label="13">家长</el-radio>
            </el-radio-group>
          </el-col>
          <el-col style="padding-bottom:0.6rem;" :span="5">
            <el-input placeholder="请输入姓名" suffix-icon="el-icon-search" v-model="input2" @change="handleIconClick"></el-input>
          </el-col>
          <el-col :span="24">
            <el-table
              :data="dialogData">
              <el-table-column
                type="index"
                label="序号"
                width="100"
                align="center">
              </el-table-column>
              <el-table-column
                v-for="colume in columes"
                :key="colume.prop"
                :prop="colume.prop"
                :label="colume.label"
                align="center">
              </el-table-column>
            </el-table>
          </el-col>
        </el-col>
      </el-row>
    </el-dialog>
    <el-row class="pageAlerts">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="currentPage"
        :page-size="10"
        layout="prev, pager, next, jumper"
        :total="total">
      </el-pagination>
    </el-row>
  </div>
</template>
<script>
  import req from './../../../../assets/js/common'
  import formatdata from './../../../../assets/js/date'
  export default{
    data(){
      return {
        isLoading:false,
        radio:14,
        input2: '',
        teacherState:true,
        dialogTableVisible: false,
        tableData: [],
        dialogData: [],
        columes:[],
        checkedAll: false,
        multipleSelection: [],
        isFilter:false,
        currentPage: 1,
        pageALl: 10,
        total:0,
        order:'createTime desc',
        cur_row_id:''
      }
    },
    created(){
      this.getList(1);
    },
    methods: {
      getList(page){
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        let param={
          page:this.currentPage,
          count:10,
          order:this.order
        };
        req.ajaxSend('/school/Notification/log','post',param, (res)=> {
          if(res.data){
            res.data.forEach((val)=>{
              val.createTime=new Date((val.createTime)*1000);
              val.createTime=formatdata.format(val.createTime,'yyyy-MM-dd HH:mm:ss');
              val.checked=false;
              val.state=val.ratio.split('/')[0]!==val.ratio.split('/')[1];
            });
          }
          this.tableData =res.data;
          this.total = res.total;
          this.isLoading=false;
        });
      },
      handleSelectionChange(val) {
        this.multipleSelection = val.map(val=>{
          val.checked = true;
          return val;
        });
      },
      showDialogTable(row){
        this.radio=14;
        this.cur_row_id = row.id;
        let param={
          type:'list',
          id:row.id,
          roleId:14
        };
        this.columes = [
          {
            prop:'name',
            label:'姓名'
          }
        ];
        req.ajaxSend('/school/Notification/log','post',param, (res)=> {
          this.dialogData = res.data;
          this.dialogTableVisible = true;
        });
      },
      handleIconClick() {
        this.changeData(this.radio,(list)=>{
          if (this.input2 !== '') {
            this.dialogData=list.filter(val=>{
              return (val.name.indexOf(this.input2)>-1);
            })
          }else{
            this.dialogData = list;
          }
        });
      },
      chooseAll(){
        if (this.checkedAll) {
          for (let obj of this.tableData) {
            obj.checked = true;
          }
          $.extend(this.multipleSelection, this.tableData);
        } else {
          for (let obj of this.tableData) {
            obj.checked = false;
          }
          this.multipleSelection = [];
        }
      },
      sort(column){
        this.order = column.order?`${column.prop} ${column.order==='ascending'?'asc':'desc'}`:'';
        this.getList(1);
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.getList(val);
      },
      deleteAlerts(){
        let that=this;
        let idsarray=[];
        let param={
          type:'del',
          ids:idsarray
        };
        for(let i=0;i<that.multipleSelection.length;i++){
          idsarray.push(parseInt(that.multipleSelection[i].id));
        }
        if (!that.multipleSelection.length) {
          that.vmMsgWarning('请选择记录！');
          return false;
        }
        that.$confirm('是否确定删除该记录?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Notification/log','post',param,function (res){
            that.getList();
            if(res.status===1){
              that.vmMsgSuccess(res.msg);
            }else{
              that.vmMsgError(res.msg);
            }
          })
        }).catch(() => {

        });
      },
      changeData(val,cb){
        if(val===14){
          this.columes = [
            {
              prop:'name',
              label:'姓名'
            }
          ]
        }else if(val===15){
          this.columes = [{
            prop:'grade',
            label:'年级'
          },
            {
              prop:'className',
              label:'班级'
            },
            {
              prop:'name',
              label:'学生姓名'
            }
          ]
        }else{
          this.columes = [{
            prop:'grade',
            label:'年级'
          },
            {
              prop:'className',
              label:'班级'
            },
            {
              prop:'name',
              label:'家长姓名'
            }
          ]
        }
        let param={
          type:'list',
          id:this.cur_row_id,
          roleId:val
        };
        req.ajaxSend('/school/Notification/log','post',param, (res)=> {
          this.dialogData = res.data;
          this.dialogTableVisible = true;
          if(cb)cb(res.data);
        });
      }
    }
  }
</script>
<style scoped>
  /*弹框*/
  .viewAlerts {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .pu-speed{
    color: #4da1ff;
    cursor: pointer;
  }
  .viewAlerts h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }
  .pageAlerts {
    text-align: center;
    margin-top: 1.5rem;
  }
</style>
<style>
  .pu-dialog .el-input__inner{
    height:2rem;
    font-size: 0.875rem;
    color: #999999;
    padding-left: 1rem;
    border-radius: 1.1rem;
  }
</style>
