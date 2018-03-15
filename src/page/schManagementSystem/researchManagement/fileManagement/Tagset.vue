<template>
  <div class="FieldSet">
    <h3>标签设置</h3>
    <el-row>
      <el-col :span="17" class="alertsBtn" style="margin-top:1.8rem;">
        <el-button type="primary" style="padding:0.5rem 0.9rem;" @click="addField()">
          <i class="el-icon-plus"></i>
          <span>新增类型</span>
        </el-button>
      </el-col>
    </el-row>
    <el-col :span="24">
      <el-row class="alertsList">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          :data="tableData"
          style="width: 100%">
          <el-table-column
            prop="name"
            label="标签类型"
            align="center">
            <template slot-scope="scope">
              <span style="cursor: pointer" @click="ChangeName(scope.row)">{{scope.row.name}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="tags"
            label="标签名"
            align="center">
            <template slot-scope="scope">
              <sl-tag
                @click="changeTag(tag,scope.$index,idx)"
                :key="idx"
                v-for="(tag,idx) in scope.row.tags"
                :closable="true"
                :close-transition="false"
                @close="handleClose(idx,scope.$index,scope.row)">
                {{tag.name}}
              </sl-tag>
              <!--<el-tag-->
              <!--:key="idx"-->
              <!--v-for="(tag,idx) in scope.row.tags"-->
              <!--:closable="true"-->
              <!--:close-transition="false"-->
              <!--@close="handleClose(idx,scope.$index,scope.row)">-->
              <!--{{tag.name}}-->
              <!--</el-tag>-->
              <span class="NewfieldDetails-add" @click="addoptions(scope.$index ,scope.row)">+</span>
            </template>
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
      <el-dialog title="添加标签名" v-if="dialogTableVisible" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col style="min-height: 10rem;">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .5rem;">标签名称：</el-col>
              <el-col :span="15">
                <el-input
                  class="input-new-tag"
                  v-model="inputValue"
                  ref="saveTagInput"
                  @keyup.enter.native="addOption">
                </el-input>
              </el-col>
            </el-col>
            <el-col>
              <span v-if="Hideid">{{optionID.id}}</span>
            </el-col>
            <el-col :span="24">
              <el-col :span="2" :offset="11">
                <el-button type="primary" class="Field-save" @click="addOption">保存</el-button>
              </el-col>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="修改标签名" v-if="editTag" :modal="false" :visible.sync="editTag">
        <el-row>
          <el-col style="min-height: 10rem;">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .5rem;">标签名称：</el-col>
              <el-col :span="15">
                <el-input
                  class="input-new-tag"
                  v-model="editvalue"
                  ref="saveTagInput"
                  @keyup.enter.native="editOption">
                </el-input>
              </el-col>
            </el-col>
            <el-col>
              <span v-if="Hideid">{{optionID.id}}</span>
            </el-col>
            <el-col :span="24">
              <el-col :span="2" :offset="11">
                <el-button type="primary" class="Field-save" @click="editOption">保存</el-button>
              </el-col>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="新增类型" v-if="dialogTableVisibleother" :modal="false" :visible.sync="dialogTableVisibleother">
        <el-row>
          <el-col style="min-height: 10rem;">
            <el-col :span="24">
              <el-col :span="6" :offset="2" style="padding-top: .5rem;">标签类型名称：</el-col>
              <el-col :span="14">
                <el-input
                  v-model="setting_name"
                  @keyup.enter.native="addSetting">
                </el-input>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col :span="2" :offset="9">
                <el-button type="primary" class="Field-save" @click="addSetting()">确定</el-button>
              </el-col>
              <el-col :span="2" :offset="14">
                <el-button class="Field-save" @click="Offdialog()">取消</el-button>
              </el-col>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="修改类型" v-if="editName" :modal="false" :visible.sync="editName">
        <el-row>
          <el-col style="min-height: 10rem;">
            <el-col :span="24">
              <el-col :span="6" :offset="2" style="padding-top: .5rem;">标签类型名称：</el-col>
              <el-col :span="14">
                <el-input
                  v-model="editName_name"
                  @keyup.enter.native="editSetting">
                </el-input>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col :span="2" :offset="8">
                <el-button type="primary" class="Field-save" @click="editSetting()">确定</el-button>
              </el-col>
              <el-col :span="2" :offset="13">
                <el-button class="Field-save" @click="Offdialog()">取消</el-button>
              </el-col>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
    </el-col>
  </div>
</template>
<script>
  import req from './../../../../assets/js/common'
  import formatdata from './../../../../assets/js/date'
  import slTag from '@/components/tag.vue';
  export default{
    components:{
      slTag
    },
    data(){
      return{
        isLoading:false,
        dialogTableVisible: false,
        dialogTableVisibleother:false,
        tableData: [],
        dialogData:[],
        tagsData:[],
        setting_name:'',
        inputVisible: false,
        inputValue: '',
        optionID:'',
        Hideid:false,
        editTag:false,
        rootIndex2edit:0,
        index2edit:0,
        tagid:'',
        editid:'',
        editvalue:'',
        editName_name:'',
        editName:false,
        editName_id:'',
      }
    },
    created(){
      this.getSettingList();
    },
    methods:{
      ChangeName(row){
        this.editName_id=row.id;
        this.editName_name=row.name;
        this.editName=true;
      },
      editSetting(){
        if(!this.editName_name){
          this.vmMsgWarning( '请填写标签类型名称！' ); return;
        }
        let param={
          type:'save',
          name:this.editName_name,
          id:this.editName_id
        };
        req.ajaxSend('/school/FileManage/tagSetting','post',param,(res)=>{
          this.getSettingList();
          if(res.status===1){
            this.vmMsgSuccess( res.msg );
            this.editName=false;
          } else{
            this.vmMsgError( res.msg );
          }
        });
      },
      changeTag(row,rootIndex,index){
        this.editid=row.id;
        this.editvalue=row.name;
        this.rootIndex2edit = rootIndex;
        this.index2edit = index;
        this.editTag=true;
      },
      editOption(){
        if(!this.editvalue){
          this.vmMsgWarning( '请填写标签类型名称' ); return;
        }
        let param={
          type:'saveGenre',
          name:this.editvalue,
          id:this.editid
        };
        req.ajaxSend('/school/FileManage/tagSetting','post',param,(res)=>{
          this.getSettingList();
          if(res.status===1){
            this.vmMsgSuccess( res.msg );
            this.editTag=false;
          } else{
            this.vmMsgError( res.msg );
          }
        });
      },
      getSettingList(){
        this.isLoading=true;
        req.ajaxSend('/school/FileManage/tagSetting','post',{},(res)=>{
          this.tableData=res.data.map(val=>{
            return val;
          });
          this.isLoading=false;
        });
      },
      addSetting(){
        let name=this.setting_name;
        if(name===''){
          this.vmMsgWarning( '请填写标签类型名称' ); return;
        }
        else{
          let param={
            type:'addGenre',
            name:name
          };
          req.ajaxSend('/school/FileManage/tagSetting','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess( res.msg );
              this.dialogTableVisibleother=false;
            } else{
              this.vmMsgError( res.msg );
            }
            this.getSettingList();
          });
        }
      },
      handleClose(idx,rootIdx,row) {
        if(row.tags.length===1){
          this.vmMsgError( '删除失败' ); return;
        }
        let tags=row.tags,
          param={
            type:'del',
            id:tags[idx].id,
          };
        tags.splice(idx,1);
        req.ajaxSend('/school/FileManage/tagSetting','post',param, (res)=>{
          this.getSettingList();
          if(res.status===1){
            this.vmMsgSuccess( res.msg );
          } else{
            this.vmMsgError( res.msg );
          }
        });
      },
      addOption() {
        let tag=this.inputValue;
        if(tag==='' || tag===null){
          this.vmMsgWarning( '请填写标签名称' ); return;
        }
        else{
          let param={
            type:'addTag',
            name:tag,
            id:this.optionID
          };
          req.ajaxSend('/school/FileManage/tagSetting','post',param,(res)=>{
            this.getSettingList();
            if(res.status===1){
              this.vmMsgSuccess( res.msg );
              this.dialogTableVisible=false;
            } else{
              this.vmMsgError( res.msg );
            }
          });
        }
        this.inputVisible = false;
        this.inputValue = '';
      },
      addField(){
        this.setting_name='';
        this.dialogTableVisibleother=true;
      },
      addoptions(idx,row){
        this.optionID=row.id;
        this.index2edit = idx;
        this.dialogTableVisible=true;
      },
      Offdialog(){
        this.dialogTableVisibleother=false;
        this.editName=false;
      },
      Delectlist(row){
        let that=this,
          param={
            type:'del',
            id:row.id,
          };
        that.$confirm('是否确定删除该标签?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/FileManage/tagSetting','post',param,function (res){
            that.getSettingList();
            if(res.status===1){
              this.vmMsgSuccess( res.msg );
            } else{
              this.vmMsgError( res.msg );
            }
          })
        }).catch(() => {

        });
      },
    }
  }
</script>
<style lang="less" scoped>
  .FieldAdministration .el-button.FieldAdmin-titlebtn {
    background-color: #13b5b1;
    border-color: #13b5b1;
    height: 36px;
    padding: 0 .9rem;
  }
  .Field-save{
    position: absolute;
    bottom: 1.4rem;
    padding: .5rem 2.1rem;
    border-radius: 1.1rem;
  }
  .NewfieldDetails-add{
    border: 1.48px solid #F08BC5;
    display: inline-block;
    color: #F08BC5;
    cursor: pointer;
    font-weight: bold;
    margin-left: .5rem;
    width:1.6rem;
    height:1.6rem;
    line-height:1.6rem;
    text-align: center;
    vertical-align: middle;
    font-size: 1rem;
    border-radius: .3rem;
  }
  .FieldSet{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .FieldSet .el-tag .el-icon-close{
    color: #ffffff;
  }
  .Doc-dia-top{
    margin-top: 1.3rem;
  }
  .LeaveRecord-title{
    margin-top: 2rem;
    padding-bottom: 1.3rem;
    border-bottom: 1px solid #d2d2d2;
  }
  .LeaveRecord-search{
    background: #4da1ff;
    width: 7rem;
    margin-left: 1.6rem;
  }
  .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
  }
  .LeaveRecord-table-div{
    width: 100%;
    height: 2.625rem;
    border-top: 1px solid #d2d2d2;
    text-align: center;
    line-height: 2.625rem;
    box-sizing:border-box;
  }
  .LeaveRecord-table-div-final{
    border-bottom: 1px solid #d2d2d2;
  }
  .LeaveRecord-table-div-1{
    border-right: 1px solid #d2d2d2;
  }
  .LeaveRecord-dialog-title{
    display: inline-block;
    margin: auto;
    font-weight: bold;
    font-size: 16px;
    padding-bottom: 1.2rem;
  }
</style>
<style lan="less">
  .FieldSet .el-tag{
    background-color:#F08BC5 ;
    margin-left: .3rem;
    cursor: pointer;
    color: #ffffff;
  }
  .FieldSet .el-tag .el-icon-close{
    color: #ffffff;
  }
</style>
