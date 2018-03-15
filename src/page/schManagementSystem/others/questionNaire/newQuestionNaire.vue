<template>
  <div class="g-container" :key="key">
    <header v-show="isAdd=='add'" class="g-textHeader g-liOneRow">
      <h2>新建问卷</h2>
      <div>
        <!--<el-button class="radiusButton" @click="newScanClick" type="primary">预览</el-button>-->
        <el-button class="radiusButton" @click="saveClick" type="primary">保存</el-button>
      </div>
    </header>
    <header class="g-textHeader g-liOneRow" v-show="isAdd!='add'">
      <div class="g-flexStartRow g-liOneRow selfSpace">
        <el-button @click="goBackParent" class="g-gobackChart g-imgContainer RedButton">
          <img src="../../../../assets/img/commonImg/icon_return.png" />
          返回
        </el-button>
        <span class="selfCenter">问卷编辑</span>
      </div>
      <div>
        <el-button class="radiusButton" @click="handlerScanClick" type="primary">预览</el-button>
        <el-button class="radiusButton" @click="saveClick" type="primary">保存</el-button>
      </div>
    </header>
    <section class="g-liOneRow g-tree_content">
      <div class="g-QNContent">
        <header>
          <div class="g-flexStartRow">
            <span class="g-label selfCenter">选择题型:</span>
            <ul class="g-flexStartWrapRow selfSpace">
              <li class="normalLi" data-msg="0" @click="QNLiClick">单选题</li>
              <li class="normalLi" data-msg="1" @click="QNLiClick">多选题</li>
              <li class="normalLi" data-msg="2" @click="QNLiClick">问答题</li>
              <li class="normalLi" data-msg="3" @click="QNLiClick">分数题</li>
              <li class="normalLi" data-msg="4" @click="QNLiClick">段落说明</li>
            </ul>
          </div>
          <div class="g-flexStartRow g-QNSwitch">
            <el-switch v-model="anonymous" active-text="实名" inactive-text="匿名" active-color="" inactive-color="#ff5b5b"></el-switch>
            <span class="g-prompt selfCenter">注：匿名/实名收集用户问卷，该功能只对系统内用户有效。</span>
          </div>
        </header>
        <section>
          <div class="g-QNaireTitle">
            <div class="g-QNaireTitleRow">
              <span class="g-label">问卷标题:</span>
              <el-input v-model="questionName" placeholder="请填写标题，100字以内"></el-input>
            </div>
            <div class="g-QNaireTitleRow">
              <span class="g-label">问卷说明:</span>
              <el-input v-model="explain" placeholder="请填写问卷说明，2000字以内"></el-input>
            </div>
          </div>
          <div class="g-QNSetContainer" v-for="(content,index) in QuestionNaireForm.QNArray">
            <div v-if="content.type==0" class="g-singleTopic">
              <!--单选题 data-msg="5" @click="QNLiClick"-->
              <div class="g-QNSetTopicContainer g-liOneRow">
                <h2 v-text="'Q'+content.QNum"></h2>
                <div class="g-QNSetContent selfSpace">
                  <el-input v-model="content.title" class="g-QRTitle" placeholder="请输入单选题题目"></el-input>
                  <div class="g-addQuestion">
                    <div class="g-addQuestionRow g-liOneRow" v-for="(question,qIndex) in content.QNSetting">
                      <el-radio v-model="content.index" disabled class="selfCenter g-chooseBox"></el-radio>
                      <el-input v-model="question.value" placeholder="请输入单选选项"></el-input>
                      <div class="g-QNHandlerContainer topicHandler">
                        <span class="icon_Add" :data-id="index" @click="addTopicClick"></span>
                        <span class="icon_goUp" data-msg="top" :data-id="index+'_'+qIndex" @click="topicGoUpClick"></span>
                        <span class="icon_goDown" data-msg="down" :data-id="index+'_'+qIndex" @click="topicGoUpClick"></span>
                        <span class="icon_shutDown" :data-id="index+'_'+qIndex" @click="deleteTopicClick"></span>
                      </div>
                    </div>
                    <div class="batchAdd" data-msg="radio" :data-id="index" @click="batchAddClick">
                      <img class="batchAddNormal" title="批量添加选项" src="../../../../assets/img/commonImg/btn_bulkadd_n.png" />
                      <img class="batchAddActive" title="批量添加选项" src="../../../../assets/img/commonImg/btn_bulkadd_highlight.png" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="g-QNSetFooter g-liOneRow">
                <el-checkbox class="g-QRSetMust" v-model="content.isMust">必填项<span>(单选题)</span></el-checkbox>
                <div class="g-QNHandlerContainer">
                  <span class="icon_goUp" data-msg="top" :data-id="index" @click="ProblemTypeGoUpClick"></span>
                  <span class="icon_goDown" data-msg="down" :data-id="index" @click="ProblemTypeGoUpClick"></span>
                  <span class="icon_shutDown" :data-id="index" @click="deleteProblemTypeClick"></span>
                </div>
              </div>
            </div>
            <div v-if="content.type==1" class="g-multipleChoose">
              <!--多选题-->
              <div class="g-QNSetTopicContainer g-liOneRow">
                <h2 v-text="'Q'+content.QNum"></h2>
                <div class="g-QNSetContent selfSpace">
                  <el-input v-model="content.title" class="g-QRTitle" placeholder="请输入多选题题目"></el-input>
                  <div class="g-addQuestion">
                    <div class="g-addQuestionRow g-liOneRow" v-for="(question,qIndex) in content.QNSetting">
                      <el-checkbox v-model="question.isChoose" class="selfCenter g-chooseBox" disabled></el-checkbox>
                      <el-input v-model="question.value" placeholder=""></el-input>
                      <div class="g-QNHandlerContainer topicHandler">
                        <span class="icon_Add" data-msg="check" :data-id="index" @click="addTopicClick"></span>
                        <span class="icon_goUp" data-msg="top" :data-id="index+'_'+qIndex" @click="topicGoUpClick"></span>
                        <span class="icon_goDown" data-msg="down" :data-id="index+'_'+qIndex" @click="topicGoUpClick"></span>
                        <span class="icon_shutDown" data-msg="check" :data-id="index+'_'+qIndex" @click="deleteTopicClick"></span>
                      </div>
                    </div>
                    <div class="batchAdd" :data-id="index" @click="batchAddClick" data-msg="check">
                      <img class="batchAddNormal" title="批量添加选项" src="../../../../assets/img/commonImg/btn_bulkadd_n.png" />
                      <img class="batchAddActive" title="批量添加选项" src="../../../../assets/img/commonImg/btn_bulkadd_highlight.png" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="g-QNSetFooter g-liOneRow">
                <div class="g-flexStartRow">
                  <el-checkbox class="g-QRSetMust" v-model="content.isMust">必填项<span>(多选题)</span></el-checkbox>
                  <div class="g-optionNum">
                    <span>最多可选</span>
                    <el-input-number class="g-courseNum" v-model="content.optionNum" :min="1" :max="maxOptionNum"></el-input-number>
                    <span>项</span>
                  </div>
                </div>
                <div class="g-QNHandlerContainer">
                  <span class="icon_goUp" data-msg="top" :data-id="index" @click="ProblemTypeGoUpClick"></span>
                  <span class="icon_goDown" data-msg="down" :data-id="index" @click="ProblemTypeGoUpClick"></span>
                  <span class="icon_shutDown" :data-id="index" @click="deleteProblemTypeClick"></span>
                </div>
              </div>
            </div>
            <div v-if="content.type==2" class="g-fillInBlank">
              <!--填空题-->
              <div class="g-QNSetTopicContainer g-liOneRow">
                <h2 v-text="'Q'+content.QNum"></h2>
                <div class="g-QNSetContent selfSpace">
                  <el-input v-model="content.title" class="g-QRTitle" placeholder="请输入填空题题目"></el-input>
                  <div class="g-addQuestion">
                    <div class="g-addQuestionRow g-liOneRow">
                      <el-input v-model="content.QNSetting.value" disabled placeholder=""></el-input>
                    </div>
                  </div>
                </div>
              </div>
              <div class="g-QNSetFooter g-liOneRow">
                <div class="g-flexStartRow">
                  <el-checkbox class="g-QRSetMust" v-model="content.isMust">必填项<span>(填空题)</span></el-checkbox>
                </div>
                <div class="g-QNHandlerContainer">
                  <span class="icon_goUp" data-msg="top" :data-id="index" @click="ProblemTypeGoUpClick"></span>
                  <span class="icon_goDown" data-msg="down" :data-id="index" @click="ProblemTypeGoUpClick"></span>
                  <span class="icon_shutDown" :data-id="index" @click="deleteProblemTypeClick"></span>
                </div>
              </div>
            </div>
            <div v-if="content.type==3" class="g-scoresTopic">
              <!--分数题-->
              <div class="g-QNSetTopicContainer g-liOneRow">
                <h2 v-text="'Q'+content.QNum"></h2>
                <div class="g-QNSetContent selfSpace">
                  <el-input v-model="content.title" class="g-QRTitle" placeholder="请输入分数题题目"></el-input>
                  <div class="g-addQuestion">
                    <div class="g-addQuestionRow g-liOneRow">
                      <el-input v-model="content.QNSetting.value" disabled placeholder=""></el-input>
                    </div>
                  </div>
                </div>
              </div>
              <div class="g-QNSetFooter g-liOneRow">
                <div class="g-flexStartRow">
                  <el-checkbox class="g-QRSetMust selfCenter" v-model="content.isMust">必填项<span>(分数题)</span></el-checkbox>
                  <div class="g-optionNum">
                    <span>限定最大值</span>
                    <el-input class="defineInputWidth" v-model="content.maxScore"></el-input>
                    <span>分</span>
                  </div>
                  <div class="g-prompt selfCenter">注：此题只能用数字作答，且可在问卷统计中统计求和、平均结果。</div>
                </div>
                <div class="g-QNHandlerContainer">
                  <span class="icon_goUp" data-msg="top" :data-id="index" @click="ProblemTypeGoUpClick"></span>
                  <span class="icon_goDown" data-msg="down" :data-id="index" @click="ProblemTypeGoUpClick"></span>
                  <span class="icon_shutDown" :data-id="index" @click="deleteProblemTypeClick"></span>
                </div>
              </div>
            </div>
            <div v-if="content.type==4" class="g-paragraphDescript">
              <!--段落说明-->
              <div class="g-QNSetTopicContainer g-liOneRow">
                <div class="g-QNParagraph selfSpace">
                  <div class="g-addQuestion">
                    <div class="g-addQuestionRow g-liOneRow">
                      <el-input v-model="content.description" placeholder="段落说明"></el-input>
                    </div>
                  </div>
                </div>
              </div>
              <div class="g-QNSetFooter g-flexEndRow">
                <div class="g-QNHandlerContainer">
                  <span class="icon_goUp" data-msg="top" :data-id="index" @click="ProblemTypeGoUpClick"></span>
                  <span class="icon_goDown" data-msg="down" :data-id="index" @click="ProblemTypeGoUpClick"></span>
                  <span class="icon_shutDown" :data-id="index" @click="deleteProblemTypeClick"></span>
                </div>
              </div>
            </div>
          </div>
          <div class="g-batchAdd"><!--批量添加--></div>
        </section>
      </div>
      <div class="g-sectionL">
        <header class="gL-header">
          <h2>候选人员名单</h2>
          <el-input @input="fuzzyClick" v-model="fuzzyInput" class="fuzzyInput" placeholder="请输入查询班级或姓名" suffix-icon="el-icon-search"></el-input>
        </header>
        <section class="gL-section">
          <el-tree
            v-loading="loading"
            element-loading-text="拼命加载中"
            element-loading-spinner="el-icon-loading"
            :render-after-expand="true"
            :highlight-current="true"
            :data="treeData"
            :default-checked-keys="checkedArr"
            :props="defaultProps"
            ref="allMsg"
            :filter-node-method="filterNode"
            show-checkbox
            @check-change="handlerCheckChange"
            node-key="wyId">
          </el-tree>
<!--          <div class="g-treeBInput">
            <el-input placeholder="手动添加学生名字" v-model="studentName">
            </el-input>
            <span class="searchBtn" @click="goSearchStudent">
            </el-input>
              <i class="el-icon-plus"></i>
            </span>
          </div>-->
        </section>
      </div>
    </section>
    <el-dialog class="headerNotBackground" title="批量添加选项" :modal="false" :visible.sync="isDialog" :before-close="handlerDetailClose">
      <el-input id="text1" type="textarea" placeholder="选项1<br/>选项2" :autosize="{minRows:10}" v-model="batchTextarea"></el-input>
      <div class="g-prompt">温馨提示:每行代表一个选项，回车添加多个选项</div>
      <div class="g-button">
        <el-button type="primary" @click="addOptionBatch">添加</el-button>
        <el-button @click="cancelAddOptionBatch">关闭</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {mapActions,mapState} from 'vuex'
  import {
    /*编辑*/
    handlerQuestionNaireLoad,//得到编辑问卷信息
    handlerQuestionNaireSave,//保存
    handlerQuestionNaireScanSave,//预览
    handlerQuestionNaireScanLoad,//预览后加载
    /*新建问卷*/
    questionNairePerson,//得到候选人
    newQuestionNaireSave,//保存
  } from '@/api/http'
  import ElCheckbox from "../../../../../node_modules/element-ui/packages/checkbox/src/checkbox";
  export default{
    components: {ElCheckbox},
    data(){
      let _self=this;
      return{
        /*判断是编辑问卷还是新建问卷*/
        isAdd:'',
        /*是否实名*/
        anonymous:false,
        /*问卷题目设置*/
        QNumText:0,
        questionName:'',//问卷标题
        explain:'',//问卷说明
        QuestionNaireForm:{
          /*单选多选题等数据总和*/
          QNArray:[
            /*0为单选题，依次往下*/
          ],
        },
        /*多选最多选项，最大值设置*/
        maxOptionNum:2,
        /*单选题默认数据*/
        singleTopic:{
          /*QNum:_self.QNumText,*///第几题
          type:'0',
          isMust:false,//必选题?
          title:'',
          QNSetting:[
            {
              isChoose:0,
              value:'',//选项值
            },
            {
              isChoose:0,
              value:'',//选项值
            },
          ]
        },
        /*多选题默认数据*/
        multipleChoose:{
          /*QNum:_self.QNumText,*///第几题
          type:'1',
          title:'',
          isMust:false,//必选题?
          optionNum:1,//最多可选
          QNSetting:[
            {
              isChoose:0,
              value:'',//选项值
            },
            {
              isChoose:0,
              value:'',//选项值
            },
          ]
        },
        /*问答题默认数据*/
        fillInBlank:{
          /*QNum:_self.QNumText,*///第几题
          type:'2',
          title:'',
          isMust:false,//必选题?
          QNSetting:{
            value:'',//选项值
          },
        },
        /*分数题默认数据*/
        scoresTopic:{
          /*QNum:_self.QNumText,*///第几题
          type:'3',
          title:'',
          isMust:false,//必选题?
          maxScore:'100',//限定最高分
          QNSetting:{
            value:'',//选项值
          },
        },
        /*段落说明默认数据*/
        paragraphDescript:{
          type:'4',
          description:'',
        },
        /*选项默认数据*/
        ChooseTopic:{
          isChoose:0,
          value:'',//选项值
        },
        /*模糊查询老师*/
        fuzzyInput:'',
        /*tree*/
        treeData:[],
        treeDataMap: [],
        checkedArr:[],//默认选中项
        defaultProps: {
          children:'data',
          label:'name',
        },
        /*tree下方input框*/
        studentName:'',
        /*弹框*/
        isDialog:false,
        optionAddType:'',
        optionAddIndex:'',
        optionArray:[],//批量添加信息
        batchTextarea:'',
        /*route param*/
        handlerType:'',//页面是从哪儿跳转来的，normal是问卷记录中跳转的
        /*send ajax param*/
        questionId:'',
        checkUser:[],
        token:'',
        loading: false
      }
    },
    computed:{
      ...mapState(['scanData','questionN','explainN']),
      key(){
        return this.$route.name !== undefined? this.$route.name + +new Date(): this.$route + +new Date()
      },
    },
    methods:{
      getChildeNodes( node ){
        let data = [];
        if( node.level == 1 ){
          let secondNodes = this.treeData.find( o => o.wyId === node.data.wyId );
          // 点击的是第一级节点时，取出该节点下二级节点的数据
          data = secondNodes.data.map( obj => {
            let mapData = JSON.parse(JSON.stringify(obj)); mapData.data = [];
            return mapData;
          });
        }
        if( node.level == 2 ){
          let secondNodes = this.treeData.find( o => o.wyId === node.parent.data.wyId );
          let thirdNodes = secondNodes.data.find( o => o.wyId == node.data.wyId );
          if( thirdNodes.data instanceof Array ){
            data = thirdNodes.data.map( obj => {
              let mapData = JSON.parse(JSON.stringify(obj)); mapData.data = [];
              return mapData;
            });
          }
        }
        if( node.level == 3 ){
          let secondNodes = this.treeData.find( o => o.wyId === node.parent.parent.data.wyId );
          let thirdNodes = secondNodes.data.find( o => o.wyId == node.parent.data.wyId );
          let fourthNodes = thirdNodes.data.find( o => o.wyId == node.data.wyId );
          if( fourthNodes.data instanceof Array ){
            data = fourthNodes.data.map( obj => {
              let mapData = JSON.parse(JSON.stringify(obj)); mapData.data = [];
              return mapData;
            });
          }
        }
        return data;
      },
      loadChildren( node, resolve){
        let data = this.getChildeNodes(node);
        resolve(data);
      },
      nodeExpand(dataObj, node, comp){
        // let data = this.getChildeNodes(node);
        // this.$refs.allMsg.updateKeyChildren( node.key, data);
      },
      nodeCollapse(data, node, comp){
        //node.childNodes = [];
      },
      goBackParent(){
        this.$router.push('/questionNaireRecord');
      },
      /*header处*/
      ...mapActions(['scanDataInit']),
      scanCommonClick(){
        this.scanDataInit({content:this.QuestionNaireForm.QNArray,questionN:this.questionName,explainN:this.explain});
      },
      /*教师信息模糊查询*/
      fuzzyClick(){
        /*input框输入值改变触发的函数*/
        this.$refs['allMsg'].filter(this.fuzzyInput);
      },
      filterNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.name.indexOf(value) !== -1;
      },
      /*题目添加*/
      QNLiClick(event){
        const e=$(event.currentTarget);
        const type=e.data('msg');
              /* e.removeClass('normalLi');
              e.addClass('activeLi');
              e.siblings().removeClass('activeLi');
              e.siblings().addClass('normalLi');*/
              /*思路为储存问卷信息对象数组中添加相对应的默认信息*/
        if(type==0){
          this.QNumText=Number(this.QNumText)+1;
          this.singleTopic.QNum=this.QNumText;
          this.QuestionNaireForm.QNArray.push(JSON.parse(JSON.stringify(this.singleTopic)));
        }
        else if(type==1){
          this.QNumText=Number(this.QNumText)+1;
          this.multipleChoose.QNum=this.QNumText;
          this.QuestionNaireForm.QNArray.push(JSON.parse(JSON.stringify(this.multipleChoose)));
        }
        else if(type==2){
          this.QNumText=Number(this.QNumText)+1;
          this.fillInBlank.QNum=this.QNumText;
          this.QuestionNaireForm.QNArray.push(JSON.parse(JSON.stringify(this.fillInBlank)));
        }
        else if(type==3){
          this.QNumText=Number(this.QNumText)+1;
          this.scoresTopic.QNum=this.QNumText;
          this.QuestionNaireForm.QNArray.push(JSON.parse(JSON.stringify(this.scoresTopic)));
        }
        else if(type==4){
          /*段落说明*/
          this.QuestionNaireForm.QNArray.push(JSON.parse(JSON.stringify(this.paragraphDescript)));
        }
      },
      /*删除题目*/
      deleteProblemTypeClick(event){
        const e=$(event.currentTarget);
        const index=e.data('id');
        this.$confirm('是否删除数据？','提示',{
          confirmButtonText:'确定',
          type:'warning'
        }).then(()=>{
          this.QuestionNaireForm.QNArray.splice(index,1);
          this.QNumText=this.QNumText-1;
        }).catch(()=>{});
      },
      /*题目上移*/
      ProblemTypeGoUpClick(event){
        const e=$(event.currentTarget);
        const type=e.data('msg');
        /*题型index*/
        const index=Number(e.data('id'));
        const arr=this.QuestionNaireForm.QNArray;
        if(type=='top'){
          /*上移*/
          if(index>0){
            /*判断是否为第一个*/
            if(arr[index].type!='4' && arr[index-1].type!='4'){
              /*为段落说明*/
              arr[index].QNum=arr[index].QNum-1;
              arr[index-1].QNum=arr[index-1].QNum+1;
            }
            arr.splice(index-1,1,...arr.splice(index,1,arr[index-1]));
          }
          else{
            return
          }
        }
        else{
          /*下移*/
          if(index<arr.length-1){
            /*判断是否为最后一个*/
            if(arr[index].type!='4' && arr[index+1].type!='4'){
              /*为段落说明*/
              arr[index].QNum=arr[index].QNum+1;
              arr[index+1].QNum=arr[index+1].QNum-1;
            }
            arr.splice(index,1,...arr.splice(index+1,1,arr[index]));
          }
          else{
            return
          }
        }
      },
      /*选项批量添加*/
      batchAddClick(event){
        const e=$(event.currentTarget);
        this.isDialog=true;
        this.optionAddIndex=e.data('id');
        this.optionAddType=e.data('msg');
      },
      /*选项添加*/
      addTopicClick(event){
        const e=$(event.currentTarget);
        const index=e.data('id');
        /*添加的题型*/
        const problemType=e.data('msg');
        this.addTopicCommon(index,problemType,this.ChooseTopic);
      },
      /*选项添加公用方法*/
      addTopicCommon(index,problemType,newObj){
        /*index为向哪一条数据添加，problemType为判断是否为多选题，用于修改最大选项值*/
        this.QuestionNaireForm.QNArray[index].QNSetting.push(JSON.parse(JSON.stringify(newObj)));
        if(problemType=='check'){
          this.maxOptionNum=this.QuestionNaireForm.QNArray[index].QNSetting.length;
        }
        else{
          return false;
        }
      },
      /*选项删除*/
      deleteTopicClick(event){
        const e=$(event.currentTarget);
        /*题型的index*/
        const parentIndex=e.data('id').split('_')[0];
        /*选项的index*/
        const childIndex=e.data('id').split('_')[1];
        /*添加的题型*/
        const problemType=e.data('msg');
        if(this.QuestionNaireForm.QNArray[parentIndex].QNSetting.length>2){
          this.QuestionNaireForm.QNArray[parentIndex].QNSetting.splice(childIndex,1);
        }
        else{
          this.alertPrompt('无法删除，至少需要保留两个选项！','warning');
          return;
        }
        /*减小多选题最多选项的最大值*/
        if(problemType){
          this.maxOptionNum=this.QuestionNaireForm.QNArray[parentIndex].QNSetting.length;
          /*判断最大选项最大值与当前最大选项值哪个大*/
          if(this.maxOptionNum<this.QuestionNaireForm.QNArray[parentIndex].optionNum){
            this.QuestionNaireForm.QNArray[parentIndex].optionNum=this.maxOptionNum;
          }
          else{
            return false;
          }
        }
        else{
          return false;
        }
      },
      /*选项上移及下移*/
      topicGoUpClick(event){
        const e=$(event.currentTarget);
        const type=e.data('msg');
        /*题型index*/
        const parentIndex=Number(e.data('id').split('_')[0]);
        /*选项index*/
        const childIndex=Number(e.data('id').split('_')[1]);
        const arr=this.QuestionNaireForm.QNArray[parentIndex].QNSetting;
        if(type=='top'){
          /*上移*/
          if(childIndex>0){
            /*判断是否为第一个*/
            arr.splice(childIndex-1,1,...arr.splice(childIndex,1,arr[childIndex-1]));
          }
          else{
            return
          }
        }
        else{
          /*下移*/
          if(childIndex<arr.length-1){
            /*判断是否为第一个*/
            arr.splice(childIndex,1,...arr.splice(childIndex+1,1,arr[childIndex]));
          }
          else{
            return
          }
        }
      },
      /*tree复选框选中事件*/
      handlerCheckChange(data, checked, indeterminate){
        let checkNodes = this.$refs.allMsg.getCheckedNodes();
        this.checkUser = checkNodes.filter( o => !('data' in o) );
      },
      /*tree下方input框*/
      goSearchStudent(){//查询
      },
      /*弹框*/
      handlerDetailClose(done){
        done();
      },
      /*点击添加按钮*/
      addOptionBatch(){
        this.isDialog=false;
        this.optionArray=this.batchTextarea.split(/[\r\n]/g);
        /*数据格式化*/
        this.batchTextarea='';
        this.optionArray.forEach((value)=>{
          this.addTopicCommon(this.optionAddIndex,this.optionAddType,{isChoose:0,value:value});
        });
      },
      /*关闭弹框*/
      cancelAddOptionBatch(){
        this.batchTextarea='';
        this.isDialog=false;
      },
      /*新建问卷数据初始化*/
      newNaireInit(){
        this.anonymous=false;
        this.questionName='';
        this.explain='';
        this.checkedArr=[];//默认选中项
        this.checkUser=[];//选中项的信息数组
        this.QuestionNaireForm.QNArray=[];//content
        this.QNumText=0;
      },
      /*send ajax----------*/
      /*编辑问卷，得到问卷信息*/
      getHandlerDataAjax(){
        handlerQuestionNaireLoad({questionId:this.questionId}).then(data=>{
          if(data.statu){
            this.treeData=data.data.list;
            this.anonymous=data.data.anonymous;
            this.questionName=data.data.questionName;
            this.explain=data.data.explain;
            this.QuestionNaireForm.QNArray=data.data.content;
            this.checkedArr=data.data.check;
            this.checkUser=data.data.userList;
            /*将最后一个含有问题序号的赋值给QNumText*/
            for(let i=(this.QuestionNaireForm.QNArray.length-1);i>=0;i--){
              if('QNum' in this.QuestionNaireForm.QNArray[i]){
                this.QNumText=this.QuestionNaireForm.QNArray[i].QNum;
                break;
              }
            }
          }
          else{
            this.vmMsgError( '数据加载失败，请重试！' );
            this.treeData=[];
            this.anonymous=false;
            this.questionName='';
            this.explain='';
            this.QuestionNaireForm.QNArray=[];
            this.checkedArr=[];//默认选中项
            this.checkUser=[];//选中项的信息数组
            this.QNumText=0;
          }
        })
      },
      /*预览返回页面默认信息*/
      getHandlerScanAjax(){
        handlerQuestionNaireScanLoad({token:this.token}).then(data=>{
          if(data.statu){
            this.questionName=data.data.questionName;
            this.explain=data.data.explain;
            this.QuestionNaireForm.QNArray=data.data.content;
          }
          else{
            this.vmMsgError( '数据加载失败，请重试！' );
            this.questionName='';
            this.explain='';
            this.QuestionNaireForm.QNArray=[];
          }
        })
      },
      handlerScanClick(){
        let data = { explain:this.explain,content:this.QuestionNaireForm.QNArray,questionName:this.questionName };
        handlerQuestionNaireScanSave( data ).then(data=>{
          if(data.statu){
            this.$router.push({name:'scanQuestionNaire',params:{id:'3-'+this.questionId+'-'+data.token}});
          }
          else{
            this.vmMsgError( '预览失败，请重试！' );
          }
        });
//        this.scanCommonClick();
      },
      /*新建问卷*/
      /*得到候选人*/
      getPersonAjax(){
        this.loading = true;
        questionNairePerson().then(data=>{
          this.loading = false;
          if(data.statu){
            this.treeData = data.data;
          }
          else{
            this.vmMsgError( '候选人名单加载失败，请重试！' );
            this.treeData=[];
          }
        });
      },
      /*保存*/
      saveClick(){
        // 用于判断选择题、分数和问答题、段落说明是否填写完整
        let isConentEmptySelect = false, isConentEmptyDescription = false, isConentEmptyScore = false;

        if( this.checkUser.length <= 0 ){
          this.vmMsgWarning( '请选择候选人员名单！' ); return;
        }
        if( !this.questionName ) {
          this.vmMsgWarning('请填写问卷标题！' ); return;
        }
        if( this.QuestionNaireForm.QNArray.length <= 0 ){
          this.vmMsgWarning('请填写问卷内容！' ); return;
        }else{
          isConentEmptyScore = this.QuestionNaireForm.QNArray.filter( o => o.type != 4 ).every( o => o.title );
          isConentEmptyDescription = this.QuestionNaireForm.QNArray.filter( o => o.type == 4 ).every( o => o.description );
          this.QuestionNaireForm.QNArray.forEach( obj => {
            if(obj.QNSetting instanceof Array){
              isConentEmptySelect = obj.QNSetting.every( temp => temp.value );
            }
          });
        }
        if( !isConentEmptySelect || !isConentEmptyScore || !isConentEmptyDescription) { this.vmMsgWarning('请将问卷内容填写完整！'); return; }
        if(this.isAdd=='add'){
          newQuestionNaireSave({user:this.checkUser,explain:this.explain,content:this.QuestionNaireForm.QNArray,anonymous:this.anonymous,questionName:this.questionName}).then(data=>{
            if(data.statu){
              this.vmMsgSuccess('保存成功！');
              this.newNaireInit();
              this.$refs.allMsg.setCheckedKeys([]); // 传递空数组说明将整个树设置未未选中
            }
            else{
              this.vmMsgError('保存失败，请重试！');
            }
          });
        }
        else{
          /*发送请求*/
          handlerQuestionNaireSave({questionId:this.questionId,user:this.checkUser,explain:this.explain,content:this.QuestionNaireForm.QNArray,anonymous:this.anonymous,questionName:this.questionName}).then(data=>{
            if(data.statu){
              this.vmMsgSuccess('修改成功！');
            }
            else{
              this.vmMsgError('修改失败，请重试！');
            }
          });
        }
      },
    },
    created(){
      /*isAdd为路由信息，如果为编辑，则包含questionId*/
      this.isAdd=this.$route.params.id;
      if(this.isAdd=='add'){
        this.newNaireInit();
        this.getPersonAjax();
      }
      else{
        /*发送请求*/
        this.questionId=this.$route.params.id.split('-')[1];
        if(this.$route.params.id.split('-').length>2){
          this.token=this.$route.params.id.split('-')[2];
          this.getHandlerDataAjax();
          this.getHandlerScanAjax();
        }
        else{
          this.token='';
          this.getHandlerDataAjax();
        }
        return false;
      }
    },
    /*beforeRouteEnter(to,from,next){
      let _id;
      if('id' in from.params){
        _id=from.params.id.split('-')[0];
      }
      else{
        _id='';
      }
      next((vm)=>{
        if(_id==3){
          /!*问卷编辑*!/
          vm.getHandlerScanAjax();
        }
      });
    },*/
    beforeRouteUpdate(to,from,next){
      this.isAdd=to.params.id;
      if(this.isAdd=='add'){
        this.newNaireInit();
        this.getPersonAjax();
      }
      else{
        /*发送请求*/
        this.questionId=this.$route.params.id.split('-')[1];
        this.getHandlerDataAjax();
        return false;
      }
      next();
    },
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/test';
  @import '../../../../style/style';
  header.g-textHeader{border:none;padding-bottom:20/16rem;
    span{.fontSize(19);color:@HColor;.marginLeft(40,1582);}
  }
  .g-QNSwitch{.marginTop(15);}
  .g-prompt{margin-left:20/16rem;}
  .headerNotBackground .g-prompt{text-align:left;margin-left:0;.marginTop(10);}
  .g-container .g-tree_content{margin-top:0;}
  .g-container .g-QNContent{.width(1142,1582);}
  .g-QNContent{
    li{.widthRem(120);.height(40);text-align:center;.border-radius(4/16rem);
      &:hover{cursor:pointer;background:@buttonActive;color:#fff;border:1px solid transparent;}
    }
    li:not(:first-of-type){margin-left:30/16rem;}
    .activeLi{background:@buttonActive;color:#fff;border:none;}
    .normalLi{background:none;color:@buttonActive;border:1px solid @buttonActive;}
  }
  /*问卷设置*/
  .g-QNaireTitle{margin:20/16rem 0;}
  .g-QNaireTitleRow{.flex();justify-content:flex-start;}
  .g-QNaireTitleRow:not(:first-of-type){.marginTop(20);}
  .g-QNaireTitleRow .g-label{display:inline-block;.widthRem(80);text-align:center;align-self:center;margin-right:20/16rem;}

  /*选择题*/
  .g-QNSetContainer{padding:20/16rem 32/16rem;border:1px solid @borderColor;}
  .g-QNSetContainer:not(:first-of-type){.marginTop(20);}
  .g-QNSetContainer h2{margin-right:2%;}
  .g-QNSetContent{background:@backgroundGrad;}
  /*段落说明容器*/
  .g-QNParagraph{background:#fff;}
  .g-QNParagraph .g-addQuestion{padding-top:0;padding-bottom:0;}
  /*设置单选题内容容器*/
  .g-QNSetTopicContainer{padding-right:2rem;}
  .g-addQuestion{/*比例*/
    .width(845,980);padding:20/16rem 55/980*100%;
    .g-addQuestionRow:not(:first-of-type){.marginTop(10);}
    .topicHandler{flex-basis:9.6rem;border-left:0;.border-top-left-radius(0);.border-bottom-left-radius(0)}
  }
  .g-addQuestionRow{
    .g-chooseBox{.marginRight(15,980);}
  }
  .g-QNSetFooter{.marginTop(20);
    .g-QRSetMust{.fontSize(14);color:@normalColor;
      span{color:@buttonActive;}
      &.el-checkbox__input{margin-right:2%;}
    }
    .g-optionNum{margin-right:20/16rem;margin-left:20/16rem;
      .g-courseNum{margin:0 15/16rem;}
    }
  }
  /*操作图标*/
  .g-QNHandlerContainer{
    padding:7/16rem 12/16rem;border:1px solid @borderColor;.border-radius(4/16rem);background:#fff;
    span{display:inline-block;.widthRem(20);.height(20);font-weight:bold;
      &:hover{cursor:pointer;}
      &:hover:before{color:@buttonActive;}
    }
    span:not(:first-of-type){margin-left:7/16rem;}
  }
  /*弹框*/
  .g-hasHeightTextarea{.NotLineheight(300);overflow-x:hidden;overflow-y:auto;}
/*  .g-prompt>div{text-align:left;}
  .g-prompt>div:first-of-type{margin:15/16rem 0;.fontSize(15);}*/

  /*批量添加选项*/
  .batchAdd{.widthRem(17);.height(17);.marginTop(10);margin-left:40/16rem;
    img{width:100%;}
  }
  .batchAddNormal{display:inline-block;}
  .batchAddActive{display:none;}
  .batchAdd:hover>.batchAddNormal{display:none;cursor:pointer;}
  .batchAdd:hover>.batchAddActive{display:inline-block;cursor:pointer;}

  /*批量添加弹框*/
  .batchAddPart{width:100%;.NotLineheight(400);word-break:break-all;overflow-y:auto;border:1px solid @borderColor;padding:20/16rem;.box-sizing();
    &:focus{outline:none;}
  }
  .g-optionContent{width:100%;border:none;padding:5/16rem;.box-sizing();word-break:break-all;
    &:focus{outline:none;}
  }

</style>


