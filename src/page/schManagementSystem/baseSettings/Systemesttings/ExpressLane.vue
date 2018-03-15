<template>
  <div class="KeleiInformation">
    <el-col :span="24">
      <el-col :span="22">
        <h3>快速通道</h3>
      </el-col>
      <el-col :span="2">
        <!--<el-button type="primary" class="CreateProcess-top-btn">保存</el-button>-->
      </el-col>
    </el-col>
    <el-row>
      <el-col :span="18" class="alertsBtn" style="margin-top:1.8rem;">
        <el-button type="primary" style="padding:0.5rem 0.9rem;" @click="addKelei()">
          <i class="el-icon-plus"></i>
          <span>添加链接</span>
        </el-button>
      </el-col>
    </el-row>
    <el-col :span="24">
      <el-row class="alertsList KeleiInformation-input">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          :data="tableData"
          style="width: 100%"
          @sort-change="sort">
          <el-table-column
            type="index"
            label="序号"
            align="center"
            width="200">
          </el-table-column>
          <el-table-column
            prop="webName"
            label="网站名称"
            align="center"
            sortable="custom">
          </el-table-column>
          <el-table-column
            prop="webUrl"
            label="网址链接"
            align="center"
            sortable="custom">
            <!--<template slot-scope="scope">-->
              <!--<a :href="scope.row.webUrl"></a>-->
            <!--</template>-->
          </el-table-column>
          <el-table-column
            label="操作"
            align="center">
            <template slot-scope="scope">
              <span style="color:#ff6a6a;cursor: pointer;" @click="Delectlist(scope.row)">删除</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="添加链接" v-if="dialogTableVisibleother" :modal="false" :visible.sync="dialogTableVisibleother">
        <el-row>
          <el-col style="min-height:16rem;">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;">网站名称：</el-col>
              <el-col :span="15">
                <el-input placeholder="如：百度" v-model="param.webName"></el-input>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin-top: 2rem;">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;">网址链接：</el-col>
              <el-col :span="15">
                <el-input placeholder="如：http://www.baidu.com" v-model="param.webUrl"></el-input>
              </el-col>
            </el-col>
            <el-col :span="2" :offset="11">
              <el-button type="primary" class="Field-save" @click="SaveaddKeilei()">保存</el-button>
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
          :page-count="total">
        </el-pagination>
      </el-row>
    </el-col>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        tableData:[],
        dialogTableVisibleother:false,
        editKeilei:false,
        editData:{},
        param:{
          webName:'',
          webUrl:'',
          sortData:'',
          sort:''
        },
        currentPage: 1,
        pageALl: 10,
        total:0,

      }
    },
    created(){
      this.GetKeleiList();
    },
    methods:{
      GetKeleiList(page){
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        let param={
          page:this.currentPage,
          pageSize:10,
          sort:this.param.sort,
          sortData:this.param.sortData
        };
        req.ajaxSend('school/Systemup/faseLane?type=getList','get',param,(res)=>{
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          this.total=res.data.maxPage;
          this.tableData=res.data.data;
          this.isLoading=false;
        });
      },
      addKelei(){
        this.param.webName='';
        this.param.webUrl='';
        this.dialogTableVisibleother=true;
      },
      SaveaddKeilei(){
        if(!this.param.webName){
          this.vmMsgWarning('请填写网站名称');
          return;
        }
        if(this.tableData.some(val=>val.webName===this.param.webName)){
          this.vmMsgWarning('网站名称重复');
          return;
        }
        if(!/^(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?$/.test(this.param.webUrl)){
          this.vmMsgWarning('请输入正确的网站链接格式');
          return;
        }
        req.ajaxSend('/school/Systemup/faseLane?type=create','post',this.param,(res)=>{
          if(res.message==='success'){
            this.vmMsgSuccess('添加成功');
          }
          if(res.message==='fail'){
            this.vmMsgError('添加失败');
          }
          this.GetKeleiList();
        });
        this.dialogTableVisibleother=false;
      },
      Delectlist(row){
        this.$confirm('确定删除该链接吗?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Systemup/faseLane?type=delete','post',{id:row.id},(res)=>{
            if(res.message==='success'){
              this.vmMsgSuccess('删除成功');
            }
            if(res.message==='fail'){
              this.vmMsgError('删除失败');
            }
            this.GetKeleiList();
          });
        }).catch(() => {
        });
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.GetKeleiList(val);
      },
      sort(column){
        this.param.sort=column.prop;
        this.param.sortData =column.order?column.order==='ascending'?'inc':'desc':'';
        this.GetKeleiList();
      },
    }
  }
</script>
<style lang="less" scoped>
  .KeleiInformation{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .KeleiInformation  .CreateProcess-top-btn{
    padding:.5rem 2.8rem ;
    border-radius: 1.1rem;
  }
  .KeleiInformation  .Field-save{
    position: absolute;
    bottom: 1rem;
    padding: .5rem 2.1rem;
    border-radius: 1.1rem;
  }
  .KeleiInformation-input .el-input__inner{
    border: none;
  }
  .KeleiInformation-input .el-input__inner:focus{
    outline:1px solid #96C3F9;
  }
</style>
