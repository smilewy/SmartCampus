<template>
  <div class="g-container" v-loading="bodyloading"
       element-loading-text="拼命读取数据中...">
    <header class="g-header">
      <div class="g-textHeader g-flexStartRow">
        <el-button @click="goBackParent" class="g-gobackChart g-imgContainer RedButton">
          <img src="../../../assets/img/commonImg/icon_return.png" />
          返回
        </el-button>
        <h2 class="selfCenter">批量导入签约生</h2>
      </div>
      <div class="g-upload-section g-flexStartRow">
        <el-form ref="studentImportForm" label-position="left" :rules="importFormRules" :model="dataHeader" label-width="85px">
          <el-form-item label="文件路径:" prop="fileName">
            <div class="fileName" v-text="dataHeader.fileName"></div>
          </el-form-item>
          <div class="button_row">
            <button class="fileButtonShow headerButton">
              <img src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_choice.png" />
              选择文件
            </button>
            <input type="file" ref="uploadIpt" @change="chooseFile" class="chooseFile" name="import" title="选择文件" />
          </div>
          <div class="button_row">
            <button type="button" class="headerButton" @click="downLoadFile">
              <img src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_download.png" />
              下载模版
            </button>
          </div>
          <div class="button_row">
            <button type="button" class="headerButton" @click="saveFile">
              <img src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_upload.png" />
              保存
            </button>
          </div>
        </el-form>
      </div>
    </header>
    <section class="g-section">
<!--      <div class="gs-header g-flexEndRow">
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" @change="fuzzyClick"></el-input>
        </div>
      </div>-->
      <div class="gs-table alertsList">
        <el-table ref="studentMsgTable" :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange" @selection-change="handleStudentTable">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <el-table-column label="序号" type="index" width="55" fixed></el-table-column>
          <el-table-column label="姓名" min-width="100">
            <template slot-scope="prop">
              <el-input v-model="prop.row.name" placeholder="请输入姓名" size="mini" @change="changeSourceData(prop.$index)"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="准考证号" min-width="120">
            <template slot-scope="prop">
              <el-input v-model="prop.row.regNumber" placeholder="请输入准考证号" size="mini" @change="changeSourceData(prop.$index)"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="性别" min-width="80">
            <template slot-scope="prop">
              <el-select v-model="prop.row.sex" placeholder="请选择" size="mini" @change="changeSourceData(prop.$index)">
                <el-option key="男" label="男" value="男"></el-option>
                <el-option key="女" label="女" value="女"></el-option>
              </el-select>
            </template>
          </el-table-column>
          <el-table-column label="出生日期" min-width="150">
            <template slot-scope="prop">
               <el-date-picker style="width: 95%" @change="changeSourceData(prop.$index)" v-model="prop.row.birthday" value-format="yyyy-MM-dd" type="date" placeholder="请输入出生日期" size="mini" :editable="false"></el-date-picker>
            </template>
          </el-table-column>
          <el-table-column label="中学学校" min-width="150">
            <template slot-scope="prop">
              <el-input v-model="prop.row.secSchool" placeholder="请输入中学学校" size="mini" @change="changeSourceData(prop.$index)"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="联系方式" min-width="140">
            <template slot-scope="prop">
              <el-input v-model="prop.row.phone" placeholder="请输入联系方式" size="mini" @change="changeSourceData(prop.$index)"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="签约承诺" min-width="150">
            <template slot-scope="prop">
              <el-select v-model="prop.row.promise" size="mini" placeholder="请选择" @change="changeSourceData(prop.$index)">
                <el-option
                  v-for="item in Leveloptions"
                  :key="item.levelId"
                  :label="item.level"
                  :value="item.levelId">
                </el-option>
              </el-select>
            </template>
          </el-table-column>
          <el-table-column label="邮政编码" min-width="100">
            <template slot-scope="prop">
              <el-input v-model="prop.row.NowHomePostcode" placeholder="请输入邮政编码" size="mini" @change="changeSourceData(prop.$index)"></el-input>
            </template>
          </el-table-column>
          <!-- <el-table-column label="家庭地址" min-width="500">
            <template slot-scope="prop">
              <el-cascader style="width: 40%" :options="optionsCitys" :props="city_prop" size="mini" v-model="prop.row.homePath1"></el-cascader>
              <el-input style="width: 50%" v-model="prop.row.homePath2" placeholder="请输入详细地址" size="mini"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="户口所在地" min-width="500">
            <template slot-scope="prop">
              <el-cascader style="width: 40%" :options="optionsCitys" :props="city_prop" size="mini" v-model="prop.row.perAddress"></el-cascader>
              <el-input style="width: 50%" v-model="prop.row.perAddress" placeholder="请输入详细地址" size="mini"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="现住地址" min-width="500">
            <template slot-scope="prop">
              <el-cascader style="width: 40%" :options="optionsCitys" :props="city_prop" size="mini" v-model="prop.row.nowHomePath1"></el-cascader>
              <el-input style="width: 50%" v-model="prop.row.nowHomePath2" placeholder="请输入详细地址" size="mini"></el-input>
            </template>
          </el-table-column> -->


          <!-- <el-table-column label="序号" type="index" width="110"></el-table-column>
          <el-table-column label="姓名" prop="name"></el-table-column>
          <el-table-column label="准考证号" prop="regNumber"></el-table-column>
          <el-table-column label="性别" prop="sex"></el-table-column>
          <el-table-column label="出生日期" prop="birthday"></el-table-column>
          <el-table-column label="中学学校" prop="SecSchool"></el-table-column>
          <el-table-column label="联系方式" prop="phone"></el-table-column>
          <el-table-column label="签约承诺" prop="promise"></el-table-column>
          <el-table-column label="家庭住址" prop="homePath"></el-table-column>
          <el-table-column label="户口所在地" prop="perAddress"></el-table-column>
          <el-table-column label="邮政编码" prop="NowHomePostcode"></el-table-column>
          <el-table-column label="现住地址" prop="nowHomePath"></el-table-column> -->
        </el-table>
      </div>
    </section>
    <footer class="g-footer">
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          :page-size="pageCount"
          layout="prev, pager, next, jumper"
          :total="pageALl">
        </el-pagination>
      </el-row>
    </footer>
  </div>
</template>
<script>
  import {fileTypeCheck} from '@/assets/js/common'
  import provinceList from '@/assets/js/city'
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        bodyloading:false,
        /*ajax data*/
        headerButtonData:{
          gradeloadData:[],
          classesLoadData:[],
          msgTypeLoadData:[],
          studentBasicMsg:[],
        },
        tableImportData:[],//返回展示在页面上的所有数据
        /*form表单双向绑定数据*/
        dataHeader:{
          /*显示div中文件信息值*/
          fileName:'',
        },
        /*type='file'input框的值*/
        fileInputValue:'',
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*footer*/
        pageALl:1,
        currentPage:1,
        pageCount:10,//每页数据条数
        /*表单验证规则*/
        importFormRules:{
          fileName:[
            {required:true,message:'请选择文件!'}
          ],
        },
        Leveloptions:[],
        optionsCitys: provinceList.data,
        city_prop:{
          label:'name',
          children:'cityList'
        },
      }
    },
    computed: {},
    methods:{
      /*返回*/
      goBackParent(){
        this.$router.push('/SignUpStudentManagement');
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
      },
      sortChange(column){
        /*table排序回调*/
      },
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.changePageMsg();
      },
      GetLevel(){
        req.ajaxSend('/school/StudentIni/common','post',{func:'getLevel'},(res)=>{
          this.Leveloptions=res.data;
        });
      },
      /*更改页面展示信息*/
      changePageMsg(){
        this.headerButtonData.studentBasicMsg=[];
        if(this.currentPage*this.pageCount>this.tableImportData.length){
          /*最后一页*/
          for(let i=((this.currentPage-1)*this.pageCount);i<this.tableImportData.length;i++){
            this.headerButtonData.studentBasicMsg.push(this.tableImportData[i]);
          }
        }
        else{
          for(let i=((this.currentPage-1)*this.pageCount);i<this.currentPage*this.pageCount;i++){
            this.headerButtonData.studentBasicMsg.push(this.tableImportData[i]);
          }
        }
      },
      /*header的button群*/
      buttonClick(event){
        const e=$(event.currentTarget),targetMsg=e.data('msg');
        if(targetMsg=='add'){
          this.isDialog=true;
        }
        this.changeButtonCss(e);
      },
      changeButtonCss(target){
        const index=$('.buttonChild').index(target);
        for(let i=0,len=$('.buttonChild').length;i<len;i++){
          if(i==index){
            /*修改css*/
            if(index % 2==0){
              /*even*/
              target.css({background:'#4da1ff',borderColor:'#4da1ff'});
            }else{
              target.css({background:'#ff5b5b',borderColor:'#ff5b5b'});
            }
            /*修改img*/
            target.find('img').eq(0).css({display:'none'});
            target.find('img').eq(1).css({display:'inline-block'});
          }else{
            /*修改img*/
            $('.buttonChild').eq(i).find('img').eq(0).css({display:'inline-block'});
            $('.buttonChild').eq(i).find('img').eq(1).css({display:'none'});
            /*修改css*/
            $('.buttonChild').eq(i).css({background:'',borderColor:''});
          }
        }
      },
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
      },
      /*选择文件change事件*/
      chooseFile(event){
        let path = this.$refs.uploadIpt.value;
        let extractPath = this.extractFilename( path );
        if( !fileTypeCheck( extractPath, ['.xls', '.xlsx'] ) ) {
            this.vmMsgError( '文件不符合，请选择excel文件(*.xls或*.xlsx)！' );
            this.dataHeader.fileName = '';
        } else {
          this.dataHeader.fileName = extractPath;
          this.fileInputValue = path;
          this.uploadFile();
        }
      },
      extractFilename(path){
        let x;
        x = path.lastIndexOf('\\');
        if (x >= 0) // 基于Windows的路径
          return path.substr(x+1);
        x = path.lastIndexOf('/');
        if (x >= 0) // 基于Unix的路径
          return path.substr(x+1);
        return path; // 仅包含文件名
      },
       changeSourceData( rowNum ){
        let tempAllIndex = (this.currentPage - 1) * 9 + ( this.currentPage == 1 ? rowNum : rowNum + 1);
        let tempAllData = this.tableImportData[tempAllIndex];
        let tempCurrentPageData = this.headerButtonData.studentBasicMsg[rowNum];
        tempAllData = tempCurrentPageData;
      },
      /*send ajax*/
      /*下载模版*/
      downLoadFile(){
        req.downloadFile('.g-container','/school/StudentIni/signManage?type=download&gradeId='+this.gradeId,'post');
      },
      saveFile(){
        if( this.tableImportData.length > 0 ){
          function getTip( rowIndex, tipName ) {
            return "请填写第" + rowIndex + "行的" + tipName + "!";
          }

          let temp = this.tableImportData.find( o => !o.name || !o.regNumber );
          if(temp){
            let rowNum = this.tableImportData.indexOf(temp) + 1;
            if( !temp.name ){ this.vmMsgError(getTip( rowNum, '姓名' )); }
            if( !temp.regNumber ){ this.vmMsgError(getTip( rowNum, '准考证号' )); }
            return;
          }

          let _this = this;
          let vmLoadingIns = this.vmLoadingFull( '数据保存中，请稍后...' );
          req.ajaxSendOther('/school/StudentIni/signManage', 'post', {
            data: this.tableImportData,
            type: 'import',
            gradeId: this.gradeId
          }, function( res ){
            vmLoadingIns.close();
            if( res.status == -1 ){
              _this.vmMsgError( res.msg || "上传数据中准考证号存在重复值" );
            } else if( res.status == 3 ){
              _this.vmMsgError( res.msg || '请填写正确格式上传' );
            } else if( res.status == 2 ){
              _this.vmMsgError( res.msg || '保存失败！' );
            } else if( res.status == 0 ) {
              _this.$confirm( res.msg, '提示',  {
                  confirmButtonText: '确定',
                  cancelButtonText: '取消',
                  type: 'warning'
              }).then( () => {
                vmLoadingIns = _this.vmLoadingFull( '数据保存中，请稍后...' );
                let formData=new FormData();
                formData.append('cacheName',res.cacheName);
                formData.append('planId',res.plan);
                req.ajaxFile('/school/StudentIni/uploadCache','post', formData, ( response )=>{
                  vmLoadingIns.close();
                  _this.tableImportData=response.data;
                  _this.pageALl=response.data.length;
                  _this.currentPage=1;
                  _this.changePageMsg();
                  _this.vmMsgSuccess( response.msg );
                });
              })
            } else if( res.status == 1 ){
              _this.vmMsgSuccess( res.msg || '保存成功！' );
            }
          });
        } else {
          this.vmMsgError("请填写上传数据！");
        }
      },
      /*批量导入上传*/
      uploadFile(){
        this.$refs['studentImportForm'].validate(valid=>{
          if(valid){
            let formData=new FormData(),_self=this;
            formData.append('import',$('.chooseFile')[0].files[0]);
            formData.append('type','preview');
            formData.append('gradeId',this.gradeId);
            _self.bodyloading=true;
            req.ajaxFile('/school/StudentIni/signManage','post',formData,function(data){
              _self.$refs.uploadIpt.value = '';
              _self.tableImportData=data.data;
              _self.pageALl=data.data.length;
              _self.currentPage=1;
              _self.changePageMsg();
              if(data.status==-1){
                _self.bodyloading=false;
                _self.vmMsgWarning("上传数据中准考证号存在重复值");
                return;
              }
              if(data.status==3){
                _self.bodyloading=false;
                _self.vmMsgWarning("请填写正确格式上传");
                return;
              }
              if(data.status==0){
                _self.bodyloading=false;
                _self.$confirm('准考证号重复，将用新记录覆盖旧记录，是否继续添加', '提示', {
                  confirmButtonText: '确定',
                  cancelButtonText: '取消',
                  type: 'warning'
                }).then(() => {
                  let vmLoadingIns = this.vmLoadingFull( '数据保存中，请稍后...' );
                  formData.append('cacheName',data.cacheName);
                  formData.append('planId',data.plan);
                  req.ajaxFile('/school/StudentIni/uploadCache','post',formData,(res)=>{
                    vmLoadingIns.close();
                    _self.tableImportData=res.data;
                    _self.pageALl=res.data.length;
                    _self.currentPage=1;
                    _self.changePageMsg();
                    _self.vmMsgSuccess( res.msg);
                  });
                }).catch(() => { vmLoadingIns.close() });
              }
              if(data.status==1){
                _self.bodyloading=false;
                _self.$refs['studentImportForm'].resetFields();
              }
              else{
                _self.bodyloading=false;
                _self.vmMsgError(data.msg);
              }
            });
          }
          else{
            return false;
          }
        });
      },
    },
    created(){
      this.gradeId=this.$route.params.param;
      this.GetLevel();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
  .g-container{
    .g-textHeader{
      h2{.marginLeft(40,1582);}
    }
    .g-prompt{text-align:left;}
  }
</style>


