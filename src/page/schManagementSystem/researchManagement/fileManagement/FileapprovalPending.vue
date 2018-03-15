<template>
  <div class="FileapprovalPending">
    <el-col :span="24" class="FilerecordPending-border"></el-col>
    <el-col :span="5" :offset="19" class="Infor-input-inner" style="margin-top:1.8rem;">
      <el-input style="border-radius:1rem" placeholder="请输入搜索内容" suffix-icon="el-icon-search" v-model="key" @change="handleIconClick"></el-input>
    </el-col>
    <el-col :span="24" style="margin-top: 1.4rem">
      <el-row class="alertsList">
        <el-table
          :data="tableData"
          style="width: 100%"
          v-loading.body="isLoading"
          element-loading-text="拼命加载中...">
          <el-table-column
            prop="name"
            label="档案名称"
            align="center">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;" @click="ShowDatils(scope.row)">{{scope.row.name}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="identity"
            label="档案编号"
            align="center">
          </el-table-column>
          <el-table-column
            prop="GetName"
            label="标签"
            align="center">
          </el-table-column>
          <el-table-column
            prop="owner"
            label="档案所有者"
            align="center">
          </el-table-column>
          <el-table-column
            prop="time"
            label="档案时间"
            align="center">
            <template slot-scope="scope">
            <span>{{scope.row.time}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="creator"
            label="提交人"
            align="center">
          </el-table-column>
          <el-table-column
            prop="result"
            label="提交时间"
            align="center">
            <template slot-scope="scope">
              <span>{{scope.row.createTime}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="remark"
            label="备注"
            align="center">
          </el-table-column>
          <el-table-column
            label="操作"
            align="center"
            width="200">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right:.6rem" @click="Showpending(scope.row)">审批</span>
              <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding:0 .6rem" @click="Showedit(scope.row)">编辑</span>
              <span style="color:#4da1ff;cursor: pointer;padding-left: .6rem" @click="Showaccessory(scope.row.fileId)">附件</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="档案审批详情" v-if="dialogTableVisible" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col :span="24" style="text-align: center;min-height: 38rem;">
            <div class="LeaveRecord-table">
              <div class="LeaveRecord-dialog-title">#{{dialogData.name}}#</div>
              <el-col :span="24">
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">档案标签</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.GetName}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">档案所有者</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.owner}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">档案时间</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.time | formatDate}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">提交时间</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.createTime}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">提交人</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.creator}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div LeaveRecord-table-div-final">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">备注</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.remark}}</div>
                  </el-col>
                </div>
              </el-col>
              <el-col :span="24">
                <div class="LeaveRecord-state-btn">审批状态</div>
              </el-col>
              <el-col :span="18" :offset="2" style="margin-top: 1.8rem">
                <el-col :span="24" style="padding-bottom: 1.25rem">
                  <el-col :span="5">
                    <span>审批人：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <el-select v-model="person" @change="chooseApproval(person)">
                      <el-option
                        v-for="item in approvalData"
                        :key="item.name"
                        :value="item.name">
                      </el-option>
                    </el-select>
                  </el-col>
                </el-col>
                <el-col :span="24" style="padding-bottom: 1.25rem;">
                  <el-col :span="5">
                    <span>审批结果：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <span>{{approvalData.length?approvalData[approvalIndex].result||'无' : '无'}}</span>
                  </el-col>
                </el-col>
                <el-col :span="24" style="padding-bottom: 1.25rem;">
                  <el-col :span="5">
                    <span>审批意见：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <span>{{approvalData.length?approvalData[approvalIndex].opinion||'无' : '无'}}</span>
                  </el-col>
                </el-col>
              </el-col>
            </div>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="附件" v-if="accessory" :modal="false" :visible.sync="accessory">
        <el-row class="" style="min-height:20rem">
          <el-table
            :data=" accessoryData"
            style="width: 100%"
            border>
            <el-table-column
              type="index"
              label="序号"
              align="center"
              width="100">
            </el-table-column>
            <el-table-column
              prop="accessoryName"
              label="附件名"
              align="center">
            </el-table-column>
            <el-table-column
              label="操作"
              align="center">
              <template slot-scope="scope">
                <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right:.6rem" @click="previewAcc(scope.row)">预览</span>
                <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right:.6rem" @click="download(scope.row)">下载</span>
                <span style="color:#ff6a6a;cursor: pointer;" @click="Delectlist(scope.row)">删除</span>
              </template>
            </el-table-column>
          </el-table>
        </el-row>
        <el-row>
          <el-col :span="24" class="FilerecordPending-add-top">
            <el-col :span="3" class="FilerecordPending-add-title" style="margin-top: 1rem">上传附件：</el-col>
            <el-col :span="21">
              <div class="uploadFile">
                <el-button>选择附件</el-button>
                <input type="file" accept=".xlsx,.xlsm,.xls.doc,.docx,.ppt,.png,.jpg,.jpeg,.txt" class="file_input" @change="sendFile">
              </div>
            </el-col>
          </el-col>
          <el-col :span="24" class="FilerecordPending-add-top">
            <el-col :offset="3" :span="21" class="FilerecordPending-add-title">
              <div v-for="(file,index) in fileList">
                <span :title="file.name" style="margin:.6rem 0">{{file.name}}</span>
                <i class="el-icon-close" @click="deleteFile(index)"></i>
              </div>
            </el-col>
          </el-col>
          <el-col :span="2" :offset="11">
            <el-button type="primary" class="Field-save" :loading="save===true" @click="uploadAcc()">上传</el-button>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="档案审批"  v-if="showpending" :modal="false" :visible.sync="showpending">
       <el-row>
         <el-col :span="24" style="text-align: center;height:38rem;overflow-y: auto;">
           <div class="LeaveRecord-table">
             <div class="LeaveRecord-dialog-title">#{{dialogData.name}}#</div>
             <el-col :span="24">
               <div class="LeaveRecord-table-div">
                 <el-col :span="7">
                   <div class="LeaveRecord-table-div-1">档案标签</div>
                 </el-col>
                 <el-col :span="17">
                   <div>{{dialogData.GetName}}</div>
                 </el-col>
               </div>
               <div class="LeaveRecord-table-div">
                 <el-col :span="7">
                   <div class="LeaveRecord-table-div-1">档案所有者</div>
                 </el-col>
                 <el-col :span="17">
                   <div>{{dialogData.owner}}</div>
                 </el-col>
               </div>
               <div class="LeaveRecord-table-div">
                 <el-col :span="7">
                   <div class="LeaveRecord-table-div-1">档案时间</div>
                 </el-col>
                 <el-col :span="17">
                   <div>{{dialogData.time}}</div>
                 </el-col>
               </div>
               <div class="LeaveRecord-table-div">
                 <el-col :span="7">
                   <div class="LeaveRecord-table-div-1">提交时间</div>
                 </el-col>
                 <el-col :span="17">
                   <div>{{dialogData.createTime}}</div>
                 </el-col>
               </div>
               <div class="LeaveRecord-table-div">
                 <el-col :span="7">
                   <div class="LeaveRecord-table-div-1">提交人</div>
                 </el-col>
                 <el-col :span="17">
                   <div>{{dialogData.creator}}</div>
                 </el-col>
               </div>
               <div class="LeaveRecord-table-div LeaveRecord-table-div-final">
                 <el-col :span="7">
                   <div class="LeaveRecord-table-div-1">备注</div>
                 </el-col>
                 <el-col :span="17">
                   <div>{{dialogData.remark}}</div>
                 </el-col>
               </div>
             </el-col>
             <el-col :span="24">
               <div class="LeaveRecord-state-btn">审批状态</div>
             </el-col>
             <el-col :span="18" :offset="2" style="margin-top: 1.8rem">
               <el-col :span="24" style="padding-bottom: 1.25rem;">
                 <el-col :span="5">
                   <span>审批结果：</span>
                 </el-col>
                 <el-col :span="12" style="text-align: left;font-size: 0;">
                  <span v-for="(list,index) in textLists"
                        :class="{ active:changecolor == index}" @click="toggleClass(index,list)"
                        class="LeaveRecord-agreed">{{list.text}}</span>
                 </el-col>
               </el-col>
               <el-col :span="24" style="padding-bottom: 1.25rem;">
                 <el-col :span="5">
                   <span>审批意见：</span>
                 </el-col>
                 <el-col :span="12" style="text-align: left">
                   <textarea v-model="dialogData.passReasonText" style="resize:none;border-radius: .5rem;width: 100%;padding: 3px;" rows="6"></textarea>
                 </el-col>
               </el-col>
               <el-col :span="12" :offset="5" style="padding-bottom: 1.25rem;">
                 <el-select @change="changeText" v-model="dialogData.passReason" placeholder="常用审批意见" style="width: 100%">
                   <el-option
                     v-for="item in optionsother"
                     :key="item.value"
                     :value="item.value">
                   </el-option>
                 </el-select>
               </el-col>
               <el-col :span="12" :offset="5" style="padding-bottom: 1.25rem;"><el-button type="primary" @click="confirm()" class="LeaveRecord-search">确认</el-button></el-col>
             </el-col>
           </div>
         </el-col>
       </el-row>
      </el-dialog>
      <el-dialog title="编辑档案审批" v-if="showedit" :modal="false" :visible.sync="showedit">
        <el-row>
          <el-col :span="22" :offset="1" class="FilerecordPending-add">
            <el-col :span="24">
              <el-col :span="3" class="FilerecordPending-add-title">档案名称：</el-col>
              <el-col :span="8">
                <el-input v-model="addFileData.name"></el-input>
              </el-col>
              <el-col :span="3" :offset="2" class="FilerecordPending-add-title">档案所有者：</el-col>
              <el-col :span="8">
                <el-select style="width: 100%;" v-model="addFileData.ownerId" @change="chooseOwner" multiple placeholder="请选择">
                  <el-option
                    v-for="item in ownerArr"
                    :key="item.id"
                    :label="item.value"
                    :value="item.id">
                  </el-option>
                </el-select>
              </el-col>
            </el-col>
            <el-col :span="24" class="FilerecordPending-add-top">
              <el-col :span="3" class="FilerecordPending-add-title">时间：</el-col>
              <el-col :span="8">
                <el-date-picker
                  style="width: 100%"
                  v-model="addFileData.time"
                  type="date"
                  :editable="false"
                  placeholder="选择日期"
                  :picker-options="pickerOptions0">
                </el-date-picker>
              </el-col>
              <el-col :span="3" :offset="2" class="FilerecordPending-add-title">备注：</el-col>
              <el-col :span="8">
                <el-input v-model="addFileData.remark"></el-input>
              </el-col>
            </el-col>
            <el-col :span="24" class="FilerecordPending-add-top">
              <el-col :span="3" class="FilerecordPending-add-title">选择标签：</el-col>
              <el-col :span="21">
                是否选中上次所选标签：
                <el-switch
                  v-model="changeTagState"
                  @change="isRememberLast"
                  active-text="是"
                  inactive-text="否">
                </el-switch>
              </el-col>
            </el-col>
            <el-col :span="24" class="FilerecordPending-add-top">
              <el-col :span="21" :offset="3" class="FilerecordPending-add-Remarks">注：每种标签类型都要选择</el-col>
            </el-col>
            <el-col :span="24" class="FilerecordPending-add-top">
              <el-col :span="21" :offset="3" :key="row.id" v-for="row in Alltags">
                <el-checkbox :indeterminate="row.hasChecked" style="margin-top:.8rem;" v-model="row.allChecked" @change="selectAll(row)">{{row.name}}</el-checkbox>
                <div style="margin: 15px 0;"></div>
                <div>
                  <el-checkbox v-for="tag in row.tags"  @change="checkTag(row)" :label="tag.name" :key="tag.id" v-model="tag.checked">{{tag.name}}</el-checkbox>
                </div>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin-top: 2rem;">
              <el-col :span="2" :offset="9">
                <el-button type="primary" class="Field-save" @click="addnewField()">保存</el-button>
              </el-col>
              <el-col :span="2" :offset="3">
                <el-button class="Field-save" @click="Offdialog()">取消</el-button>
              </el-col>
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
  import req from '../../../../assets/js/common'
  import formatdata from '../../../../assets/js/date'
    export default{
        data(){
            return{
              key:'',
              fileId:'',
              currentPage: 1,
              pageALl: 10,
              total:0,
              save:false,
              dialogTableVisible:false,
              accessory:false,
              showedit:false,
              showpending:false,
              isLoading:false,
              tableData:[],
              accessoryData:[],
              checkedAll:false,
              dialogData:{},
              approvalData:[],
              approvalIndex:0,
              person:'',
              options: [{
                value: '选项1',
                label: '黄金糕'
              }],
              value5: [],
              pickerOptions0: {
                disabledDate(time) {
                  return time.getTime() < Date.now() - 8.64e7;
                }
              },
              addFileData:{
                fileId:'',
                name:'',
                ownerId:[],
                owner:[],
                tag:[],
                time:new Date(),
                remark:'',
              },
              Alltags:[],
              fileList:[],
              ownerArr: [],
              lastTagChecked:[],
              changeTagState:false,
              textLists:[{
                text:'同意'
              },{
                text:'不同意'
              }],
              optionsother: [{
                value: '情况属实，申请通过'
              }, {
                value: '情况存在异议，申请不予通过'
              }, {
                value: '申请内容不符，申请不予通过'
              }],
              changecolor:0,
            }
        },
      created(){
        this.GetFileAppList();
      },
      methods:{
        GetFileAppList(page){
          if(page!==this.currentPage){
            this.currentPage=page;
          }
          this.isLoading=true;
          let param={
            option:1,
            page:this.currentPage,
            count:10,
            key:this.key
          };
          req.ajaxSend('/school/FileManage/fileApprove','post',param,(res)=>{
            if (res.status === 0) {
              this.tableData = [];
              this.isLoading = false;
              return;
            }
            if(res.data){
              this.tableData = res.data.map(val=>{
                val.owner=val.owner.map(subval=>{
                    return subval;
                }).join('、');
                val.GetName=val.tag.map(subval=>{
                  return subval.tag;
                }).join('、');
                return val;
              });
            }
            this.total = res.maxPage;
            this.isLoading=false;
          });
        },
        chooseOwner(ids){
          this.addFileData.owner=[];
          ids.forEach(rootVal=>{
            this.addFileData.owner.push(this.ownerArr.filter(val=>val.id===rootVal)[0].value);
          });
        },
        //是否记住上次所选标签
        isRememberLast(status){
          if(status){
            this.Alltags.forEach(val=>{
              val.tags.forEach(subVal=>{
                subVal.checked = this.lastTagChecked.some(sVal=>sVal===subVal.id);
              });
              val.tagsChecked = [];
              val.tags.forEach(function(subVal){
                if(subVal.checked)val.tagsChecked.push(subVal);
              });
              let tagsChecked = val.tagsChecked.length;
              val.allChecked = tagsChecked === val.tags.length;
              val.hasChecked = tagsChecked > 0 && tagsChecked < val.tags.length;
            });
          }else{
            this.Alltags.forEach(val=>{
              val.tags.forEach(subVal=>{
                subVal.checked = this.addFileData.tag.some(sVal=>sVal.id===subVal.id);
              });
              val.tagsChecked = [];
              val.tags.forEach(function(subVal){
                if(subVal.checked)val.tagsChecked.push(subVal);
              });
              let tagsChecked = val.tagsChecked.length;
              val.allChecked = tagsChecked === val.tags.length;
              val.hasChecked = tagsChecked > 0 && tagsChecked < val.tags.length;
            });
          }
        },
        //查看附件
        Showaccessory(id){
          this.fileId = id;//4
          let param={
            func:'getAccessory',
            param:{
              fileId:id
            }
          };
          req.ajaxSend('/school/FileManage/common','post',param,(res)=>{
            this.accessoryData=res;
            this.accessory=true;
          });
        },
        //下载附件
        download(row){
          let id=row.accId;
          req.downloadFile('.FileapprovalPending','/school/FileManage/fileApprove?type=downloadAcc&accId='+id,'post');
        },
        //上传附件
        uploadAcc(){
            if(!this.fileList.length){
              this.vmMsgWarning( '你还未选择上传的文件' ); return;
            }
          let sendFormData = new FormData();
          for (let i of this.fileList) {
            sendFormData.append('accessory[]', i.file);
          }
          sendFormData.append('type','uploadAcc');
          sendFormData.append('fileId',this.fileId);
          this.$confirm('是否确定新增该附件?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            this.save=true;
            req.ajaxFile('/school/FileManage/fileApprove', 'post', sendFormData, (res) => {
              this.save=false;
              if(res.status===1){
                this.vmMsgSuccess( res.msg );
              }else {
                this.vmMsgError( res.msg );
              }
              this.addFile = false;
              this.Showaccessory(this.fileId);
              this.fileList=[];
            });
          }).catch(() => {

          });
        },
        //预览附件
        previewAcc(row){
          req.ajaxSend('/school/FileManage/fileApprove?type=preview','get',{accId:row.accId},(res)=>{
              window.open(res[0],'_blank');
          });
        },
        Showedit(row){
          this.getFileownList();
          this.getlastTag();
          this.getallTag();
          this.addFileData=this.deepCopy(row);
          this.addFileData.owner=row.owner.split("、");
          this.addFileData.time = new Date((this.addFileData.time));
          this.showedit=true;
          this.changeTagState=false;
        },
        deepCopy(data){return JSON.parse(JSON.stringify(data))},
        addnewField(){
          this.addFileData.tag=[];
          this.Alltags.forEach(rootVal => {
            rootVal.tags.forEach(val => {
              if (val.checked) {
                this.addFileData.tag.push(val.id)
              }
            })
          });
          if (!this.addFileData.name) {
            this.vmMsgWarning( '请输入档案名称' ); return;
          }
          if (!this.addFileData.owner.length) {
            this.vmMsgWarning( '请选择档案所有者' ); return;
          }
          if (!this.addFileData.time) {
            this.vmMsgWarning( '请选择档案时间' ); return;
          }
          if (this.Alltags.some((val) => {
              return !val.allChecked && !val.hasChecked;
            })) {
              this.vmMsgWarning( '每种标签类型都要选择' ); return;
          }
          this.$confirm('是否确定修改该档案?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            this.addFileData.time = formatdata.format(this.addFileData.time,'yyyy-MM-dd');
              let param={
                type:'editFile',
                fileId:this.addFileData.fileId,
                name:this.addFileData.name,
                ownerId:this.addFileData.ownerId,
                owner:this.addFileData.owner,
                tag:this.addFileData.tag,
                time:this.addFileData.time,
                remark:this.addFileData.remark,
              };
            req.ajaxSend('/school/FileManage/fileApprove', 'post',param, (res) => {
              if(res.status===1){
                this.vmMsgSuccess( res.msg );
              }else {
                this.vmMsgError( res.msg );
              }
              this.GetFileAppList();
              this.showedit = false;
            });
          }).catch(() => {

          });
        },
        sendFile(){
          var fileType, suffix, file = $('.file_input').prop('files')[0];
          if (!file) {
            return false;
          }
          $('.file_input').val('');
          if (this.fileList.length >= 3) {
            this.vmMsgWarning( '最多只能上传三个附件！' ); return false;
          }
          var sAry = file.name.split('.'), sL = sAry.length - 1;
          suffix = sAry[sL];
          for (let obj of this.fileList) {
            if (file.name == obj.name) {
              this.vmMsgWarning( '你已添加过该文件！' ); return false;
            }
          }
          this.fileList.push({'fileType': fileType, 'name': file.name, 'file': file});
        },
        deleteFile(idx){   //删除文件
          this.fileList.splice(idx,1);
        },
        Delectlist(row){
          this.$confirm('是否确认删除该附件?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            let param={
              type:'delAcc',
              accId:row.accId
            };
            req.ajaxSend('/school/FileManage/fileApprove','post',param,(res)=>{
              if(res.status===1){
                this.vmMsgSuccess( '删除成功！' );
              }else {
                this.vmMsgError( res.msg );
              }
             this.Showaccessory(this.fileId);
            });
          }).catch(() => {

          });
        },
        selectAll(row) {
          row.allChecked = row.tagsChecked.length === row.tags.length;
          row.tagsChecked = [];
          row.tags.forEach(function(val){
            val.checked = !row.allChecked;
            if(val.checked)row.tagsChecked.push(val);
          });
          let tagsChecked = row.tagsChecked.length;
          row.allChecked = tagsChecked === row.tags.length;
          row.hasChecked = tagsChecked > 0 && tagsChecked < row.tags.length;
        },
        checkTag(row) {
          row.tagsChecked = [];
          row.tags.forEach(function(val){
            if(val.checked)row.tagsChecked.push(val);
          });
          let tagsChecked = row.tagsChecked.length;
          row.allChecked = tagsChecked === row.tags.length;
          row.hasChecked = tagsChecked > 0 && tagsChecked < row.tags.length;
        },
        handleIconClick(){
          this.GetFileAppList();
        },
        handleCurrentChange(val) {
          this.currentPage = val;
          this.GetFileAppList(val);
        },
        chooseApproval(name){
          this.approvalData.forEach((val,idx)=>{
            if(val.name===name){
              this.approvalIndex=idx;
            }
          })
        },
        ShowDatils(row){
          this.approvalData=[];
          this.person='';
          row.situation=row.situation.filter(val=>val);
          if(row&&row.situation&&row.situation.length){
            for(let val in row.situation){
              this.approvalData.push(row.situation[val]);
            }
            this.person =  this.approvalData[0].name;
            this.approvalIndex = 0;
          }
          this.dialogData=row;
          this.dialogTableVisible=true;
        },
        toggleClass(index,list){
          this.changecolor = index;
        },
        changeText(){
          if(this.dialogData.passReason){
            this.dialogData.passReasonText=this.dialogData.passReason
          }
        },
        Showpending(row){
          this.changecolor=0;
          this.$set(row,'passReason','');
          this.$set(row,'passReasonText','');
          this.dialogData=row;
          this.showpending=true;
        },
        //获得所有标签
        getallTag(){
          this.Alltags = [];//1
          req.ajaxSend('/school/FileManage/common','post',{func:'getTag'},(res)=>{
            res.data.forEach(val=>{
              val.tags.forEach(subVal=>{
                subVal.checked = this.addFileData.tag.some(sVal=>sVal.id===subVal.id);
              });
              val.tagsChecked = [];
              val.tags.forEach(function(subVal){
                if(subVal.checked)val.tagsChecked.push(subVal);
              });
              let tagsChecked = val.tagsChecked.length;
              val.allChecked = tagsChecked === val.tags.length;
              val.hasChecked = tagsChecked > 0 && tagsChecked < val.tags.length;
            });
            this.Alltags=res.data;
          })
        },
        //获得上一次所选标签
        getlastTag(){
          this.lastTagChecked = [];//2
          req.ajaxSend('/school/FileManage/common','post',{func:'lastTag'},(res)=>{
            this.lastTagChecked = res.data;
          })
        },
        //获得档案所有人列表
        getFileownList(){
          this.ownerArr=[];//3
          req.ajaxSend('/school/FileManage/common','post',{func:'getOwner'},(res)=>{
            res.data.map(val=>{
              let owner = {
                id:val.id,
                value:val.name
              };
              this.ownerArr.push(owner);
            });
          })
        },
        confirm(){
          if(this.dialogData.passReasonText==='' && this.dialogData.passReason===''){
            this.vmMsgWarning( '请先填写审批意见' ); return;
          }
          let param = {
            type:'approve',
            option: 1,
            fileId:this.dialogData.fileId,
            operate:this.changecolor===0 ? 'pass':'reject',
            opinion:this.dialogData.passReasonText===''? this.dialogData.passReason:this.dialogData.passReasonText,
          };
          this.$confirm('是否确认保留该审批结果?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/FileManage/fileApprove','post',param,(res)=>{
              if(res.status===1){
                this.vmMsgSuccess( '审批成功！' );
              }else {
                this.vmMsgError( res.msg );
              }
              this.GetFileAppList(1);
              this.showpending=false;
            });
          }).catch(() => {

          });
        },
        Offedit(){
          this.showedit=false;
        },
        Offdialog(){
            this.showedit=false;
        }
      },
    }
</script>
<style lang="less" scoped>
  .FileapprovalPending .FilerecordPending-border{
    padding-top:1.6rem;
    border-bottom: 1px solid #d2d2d2;
  }
 .FileapprovalPending .LeaveRecord-search{
    background: #4da1ff;
    width: 7.6rem;
    border-radius: 1.1rem;
  }
  .FileapprovalPending .LeaveRecord-dialog-title{
    display: inline-block;
    margin: auto;
    font-weight: bold;
    font-size: 16px;
    padding-bottom: 1.2rem;
  }
  .FileapprovalPending .LeaveRecord-table-div{
    width: 100%;
    height: 2.625rem;
    border-top: 1px solid #d2d2d2;
    text-align: center;
    line-height: 2.625rem;
    box-sizing:border-box;
  }
  .FileapprovalPending .LeaveRecord-table-div-final{
    border-bottom: 1px solid #d2d2d2;
  }
  .FileapprovalPending .LeaveRecord-table-div-1{
    border-right: 1px solid #d2d2d2;
  }
  .FileapprovalPending .LeaveRecord-state-btn{
    width: 6.25rem;
    height: 1.875rem;
    background:#4ba8ff;
    color:#fff;
    text-align: center;
    line-height: 1.875rem;
    border-top-right-radius: 1.1rem;
    border-bottom-right-radius: 1.1rem;
    margin-top: 1.2rem;
  }
  .FileapprovalPending .FilerecordPending-add{
    height: 38rem;
    overflow-y: auto;
  }
  .FileapprovalPending .FilerecordPending-add .el-tag--primary{
    background-color:#F08BC5;
    border-color: #f08bc5;
    color: #fff;
  }
  .FileapprovalPending .FilerecordPending-add .el-icon-close:hover{
    background-color:#fff;
    color: #888888;
  }
  .FilerecordPending-add-title{
    /*text-align: right;*/
  }
  .FilerecordPending-add-top{
    margin-top: 1.2rem;
  }
  .FilerecordPending-add-Remarks{
    color: #A6A6A6;
  }
  .FileapprovalPending .uploadFile {
    display: inline-block;
    position: relative;
    .file_input {
      width: 100%;
      height: 36px;
      border-radius: 18/16rem;
      position: absolute;
      right: 0;
      top: 0;
      z-index: 1;
      -moz-opacity: 0;
      -ms-opacity: 0;
      -webkit-opacity: 0;
      opacity: 0; /*css属性——opcity不透明度，取值0-1*/
      filter: alpha(opacity=0); /*兼容IE8及以下--filter属性是IE特有的，它还有很多其它滤镜效果，而filter: alpha(opacity=0); 兼容IE8及以下的IE浏览器(如果你的电脑IE是8以下的版本，使用某些效果是可能会有一个允许ActiveX的提示,注意点一下就ok啦)*/
      cursor: pointer;
    }
    .el-button{
      border-radius:1.6rem;
      padding:.6rem 1.6rem;
      margin-top:.7rem;
    }
  }
  .Sendfile-text>span{
    padding:0 1rem;
    display: inline-block;
    border-right: 1px solid #d2d2d2;
  }
  .FileapprovalPending .Field-save{
    position: static;
    margin: 1.5rem 0 2.4rem 0;
    padding: .6rem 2.6rem;
    border-radius: 1.1rem;
  }

  .LeaveRecord-title{
    margin-top: 2rem;
    padding-bottom: 1.3rem;
    border-bottom: 1px solid #d2d2d2;
  }
  .LeaveRecord-search{
    background: #4da1ff;
    width: 7.6rem;
  }
  .alertsBtn{
    margin-top: 1.5rem;
  }
  .LeaveRecord-dialog-title{
    display: inline-block;
    margin: auto;
    font-weight: bold;
    font-size: 16px;
    padding-bottom: 1.2rem;
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
  .LeaveRecord-state-btn{
    width: 6.25rem;
    height: 1.875rem;
    background:#4ba8ff;
    color:#fff;
    text-align: center;
    line-height: 1.875rem;
    border-top-right-radius: 1.1rem;
    border-bottom-right-radius: 1.1rem;
    margin-top: 1.2rem;
  }
  .LeaveRecord-agreed{
    width: 5rem;
    height: 2rem;
    text-align: center;
    display: inline-block;
    line-height: 2rem;
    cursor: pointer;
    font-size: 14px;
    color: #888888;
    border: 1px solid #d2d2d2;
    border-top-left-radius: 1rem;
    border-bottom-left-radius: 1rem;
  }
  .LeaveRecord-agreed:last-of-type{
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-top-right-radius: 1rem;
    border-bottom-right-radius: 1rem;
  }
  .active{
    background: #09baa7;
    color: #fff;
    border: 1px solid #09baa7;
  }
  .agreed2{
    border-top-right-radius: 1rem;
    border-bottom-right-radius: 1rem;
  }

</style>
<style>
  .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
  }
</style>
